<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Journal;
use App\Models\Submission;
use Carbon\Carbon;

class OaiController extends Controller
{
    public function handle(Request $request, $journalPath)
    {
        // Support both slug and path for journal identification if needed, but route uses {journal} which usually binds to slug in existing logic, 
        // however here we receive a string if we don't type hint the model in the route definition.
        // The user provided code uses $journalPath.
        
        $journal = Journal::where('slug', $journalPath)
            ->orWhere('path', $journalPath) // Assuming path might be used or fallback
            ->firstOrFail();

        $verb = $request->input('verb');
        $identifier = $request->input('identifier');
        $metadataPrefix = $request->input('metadataPrefix');

        // Validation: OAI Protocol strictly requires 'verb'
        // UX Improvement: Default to Identify if accessed without params (e.g. valid browser check)
        if (!$verb) {
            return $this->identify($journal);
        }

        switch ($verb) {
            case 'Identify':
                return $this->identify($journal);

            case 'ListMetadataFormats':
                return $this->listMetadataFormats($journal);

            case 'ListRecords':
            // case 'ListIdentifiers': // Separated below
                // Validation logic moved to listRecords to support multiple formats
                // ListRecords requires metadataPrefix
                if (!$metadataPrefix && $verb === 'ListRecords') {
                     return $this->errorResponse($journal, 'badArgument', 'Missing metadataPrefix');
                }
                
                if ($metadataPrefix && $metadataPrefix !== 'oai_dc' && !in_array($metadataPrefix, ['marcxml', 'rfc1807', 'oai_marc'])) {
                    return $this->errorResponse($journal, 'cannotDisseminateFormat', 'Format not supported');
                }

                return $this->listRecords($journal, $request);

            case 'ListIdentifiers':
                if (!$metadataPrefix) {
                    return $this->errorResponse($journal, 'badArgument', 'Missing metadataPrefix');
                }
                return $this->listIdentifiers($journal, $request);

            case 'ListSets':
                // Mockup Sets OJS Structure
                // Set 1: Jurnal Utama (Root)
                // Set 2: Kategori "Articles" (Sub-set)
                $sets = [
                    (object) [
                        'spec' => strtoupper($journal->abbreviation ?? $journal->path), // Contoh: JCO
                        'name' => $journal->name,
                    ],
                    (object) [
                        'spec' => strtoupper($journal->abbreviation ?? $journal->path) . ':ART', // Contoh: JCO:ART
                        'name' => 'Articles',
                    ]
                ];
                
                // Return as text/html for "Classic" view
                return response()->view('journal.public.oai.list_sets', compact('journal', 'sets'))
                    ->header('Content-Type', 'text/html; charset=utf-8');

            case 'GetRecord':
                if (!$identifier) {
                    return $this->errorResponse($journal, 'badArgument', 'Missing identifier');
                }
                if (!$metadataPrefix) {
                    return $this->errorResponse($journal, 'badArgument', 'Missing metadataPrefix');
                }
                // Check delegated to getRecord method
                return $this->getRecord($journal, $identifier, $metadataPrefix);

            default:
                return $this->errorResponse($journal, 'badVerb', 'Illegal OAI verb');
        }
    }

    private function listIdentifiers($journal, Request $request)
    {
        // Query same as ListRecords but optimized for identifiers only
        $records = \App\Models\Submission::join('publications', 'submissions.id', '=', 'publications.submission_id')
            ->where('submissions.journal_id', $journal->id)
            ->where('submissions.status', Submission::STATUS_PUBLISHED)
            ->whereNotNull('publications.date_published')
            ->select(
                'submissions.id', 
                'submissions.slug',
                'publications.date_published as pub_date'
            )
            ->orderBy('publications.date_published', 'desc')
            ->limit(100) 
            ->get();

        return response()->view('journal.public.oai.list_identifiers', [
            'journal' => $journal,
            'records' => $records,
            'verb' => 'ListIdentifiers'
        ])->header('Content-Type', 'text/html; charset=utf-8');
    }

    private function identify($journal)
    {
        // Join with publications table to find the min(date_published)
        $earliestDate = Submission::join('publications', 'submissions.id', '=', 'publications.submission_id')
            ->where('submissions.journal_id', $journal->id)
            ->where('submissions.status', Submission::STATUS_PUBLISHED)
            ->whereNotNull('publications.date_published')
            ->min('publications.date_published');
            
        $earliestDate = $earliestDate ? Carbon::parse($earliestDate) : now();
        
        return response()->view('journal.public.oai.identify', [
            'journal' => $journal,
            'earliestDate' => $earliestDate->toIso8601String(),
            'baseUrl' => route('journal.oai', $journal->slug)
        ])->header('Content-Type', 'text/html; charset=utf-8');
    }

    private function listMetadataFormats($journal)
    {
        return response()->view('journal.public.oai.metadata_formats', [
            'journal' => $journal
        ])->header('Content-Type', 'text/html; charset=utf-8');
    }

    private function listRecords($journal, Request $request)
    {
        $prefix = $request->input('metadataPrefix');
        // 'oai_marc' is registered but we don't have a view for it yet unless mapped.
        // Assuming we only support what we have views for: oai_dc, marcxml, rfc1807.
        // If oai_marc is required, we should map it or add 'oai_marc' to this list and ensure a view exists.
        // Following prompt strictly which provided views for marcxml and rfc1807.
        // I will include oai_marc in validation as requested in Step 1 but it will fail if view is missing unless mapped.
        $validFormats = ['oai_dc', 'marcxml', 'rfc1807', 'oai_marc'];

        if (!in_array($prefix, $validFormats)) {
            return $this->errorResponse($journal, 'cannotDisseminateFormat', 'Format not supported');
        }

        // FIX: Fetch data via JOIN and Manual Selection to avoid MAX(uuid) error
        // generated by hasOne()->latestOfMany() on UUID PKs in Postgres.
        $records = Submission::join('publications', 'submissions.id', '=', 'publications.submission_id')
            ->where('submissions.journal_id', $journal->id)
            ->where('submissions.status', Submission::STATUS_PUBLISHED)
            ->whereNotNull('publications.date_published')
            ->select(
                'submissions.*', // Keep submission data
                // Alias Publication columns to avoid ambiguity
                'publications.title as pub_title',
                'publications.abstract as pub_abstract',
                'publications.date_published as pub_date',
                // 'publications.locale as pub_locale', // REMOVE: Column doesn't exist
                'publications.keywords as pub_keywords',
                'publications.doi as pub_doi'
            )
            ->with(['authors', 'issue', 'section']) 
            // CRITICAL: Removed 'publication' from eager load to stop the error
            ->orderBy('publications.date_published', 'desc')
            ->limit(100)
            ->get();

        // Manual Hydration:
        // We trick the model into thinking it loaded the relation, 
        // so the View ($record->publication->title) works perfectly.
        $records->transform(function ($submission) {
            $pub = new \App\Models\Publication();
            // Assign attributes from our aliased select
            $pub->title = $submission->pub_title;
            $pub->abstract = $submission->pub_abstract;
            $pub->date_published = $submission->pub_date;
            $pub->locale = 'en'; // Hardcoded: Column doesn't exist in DB
            $pub->keywords = $submission->pub_keywords;
            $pub->doi = $submission->pub_doi;

            // Set the relation manually
            $submission->setRelation('publication', $pub);
            
            return $submission;
        });

        // Switch View based on Prefix
        // Default View Path: journal.public.oai.formats.{prefix}
        $viewName = 'journal.public.oai.formats.' . $prefix; 
        
        // Fallback for oai_dc (since it is in the parent folder public/oai/) 
        if ($prefix === 'oai_dc') {
            $viewName = 'journal.public.oai.list_records';
        }

        // FIX: Serve HTML for oai_dc (browser view), XML for others
        $contentType = ($prefix === 'oai_dc') ? 'text/html; charset=utf-8' : 'text/xml';

        return response()->view($viewName, [
            'journal' => $journal,
            'records' => $records,
            'verb' => $request->input('verb') 
        ])->header('Content-Type', $contentType);
    }

    private function getRecord($journal, $identifier, $metadataPrefix = 'oai_dc')
    {
        $validFormats = ['oai_dc', 'marcxml', 'rfc1807', 'oai_marc'];
        if (!in_array($metadataPrefix, $validFormats)) {
             return $this->errorResponse($journal, 'cannotDisseminateFormat', 'Format not supported');
        }

        // Identifier format usually: oai:iamjos.com:article/123 or oai:iamjos.com:article/UUID
        // Format: oai:{domain}:article/{id}

        // 1. Try splitting by '/' first (Standard format defined in list_records)
        $parts = explode('/', $identifier);
        $candidate = end($parts);
        
        // 2. If the result contains ':', it means splitting by '/' failed to isolate the ID 
        // (e.g. format was oai:domain:article:123)
        if (str_contains($candidate, ':')) {
             $parts = explode(':', $identifier);
             $candidate = end($parts);
        }

        $id = $candidate;

        // Use same query style as ListRecords if possible to ensure hydration consistency,
        // but since we need just one, we filter and then hydrate manually if needed.
        // Or fetch via Model and hydrate manually if we suspect 'publication' relation issue affects single fetching too.
        // Assuming single fetch with 'with' works fine or using same manual hydration logic.
        // Let's use the manual hydration logic to be safe and consistent with ListRecords fix.
        
        $recordRaw = Submission::join('publications', 'submissions.id', '=', 'publications.submission_id')
            ->where('submissions.journal_id', $journal->id)
            ->where('submissions.id', $id)
            ->where('submissions.status', Submission::STATUS_PUBLISHED)
            ->select(
                'submissions.*',
                'publications.title as pub_title',
                'publications.abstract as pub_abstract',
                'publications.date_published as pub_date',
                'publications.keywords as pub_keywords',
                'publications.doi as pub_doi'
            )
            ->with(['authors', 'issue', 'section'])
            ->first();

        if (!$recordRaw) {
            return $this->errorResponse($journal, 'idDoesNotExist', 'Record not found');
        }

        // Hydrate
        $pub = new \App\Models\Publication();
        $pub->title = $recordRaw->pub_title;
        $pub->abstract = $recordRaw->pub_abstract;
        $pub->date_published = $recordRaw->pub_date;
        $pub->locale = 'en';
        $pub->keywords = $recordRaw->pub_keywords;
        $pub->doi = $recordRaw->pub_doi;
        $recordRaw->setRelation('publication', $pub);
        $record = $recordRaw;

        // Determine view
        if ($metadataPrefix === 'oai_dc') {
            return response()->view('journal.public.oai.get_record', [
                'journal' => $journal,
                'record' => $record
            ])->header('Content-Type', 'text/xml');
        } else {
             // For MARCXML/RFC1807, reuse the formats views which loop over $records
             $records = collect([$record]);
             $viewName = 'journal.public.oai.formats.' . $metadataPrefix;
             
             return response()->view($viewName, [
                'journal' => $journal,
                'records' => $records,
                'verb' => 'GetRecord' // Pass GetRecord as verb so the view wraps it correctly
            ])->header('Content-Type', 'text/xml');
        }

    }

    private function errorResponse($journal, $code, $message)
    {
        return response()->view('journal.public.oai.error', [
            'journal' => $journal,
            'code' => $code,
            'message' => $message
        ])->header('Content-Type', 'text/xml');
    }
}
