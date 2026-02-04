<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Journal;
use App\Models\Submission;
use Carbon\Carbon;

class OaiController extends Controller
{
    public function handle(Request $request)
    {
        // 1. Resolve Journal (From Route Parameter)
        $journalPath = $request->route('journal');
        // Support journal slug or path logic from previous context
        $journal = Journal::where('slug', $journalPath)
            ->orWhere('path', $journalPath)
            ->firstOrFail();
        
        // 2. Ambil Parameter
        $verb = $request->input('verb');
        $inputs = $request->all();
        
        // 3. Validasi Verb
        $validVerbs = ['Identify', 'ListRecords', 'ListSets', 'ListMetadataFormats', 'ListIdentifiers', 'GetRecord'];
        if (!$verb || !in_array($verb, $validVerbs)) {
            return $this->oaiError('badVerb', 'Illegal OAI verb');
        }

        // 4. Cek Resumption Token (Eksklusivitas)
        // Jika ada resumptionToken, tidak boleh ada parameter lain (selain verb)
        // Note: inputs includes 'journal' route param usually? No, $request->all() typically is GET/POST params.
        // But strictly check inputs from query string is safer.
        // Assuming $inputs strictly contains query parameters.
        if ($request->has('resumptionToken')) {
            // Count arguments. verb + resumptionToken = 2.
            $paramCount = count($request->query()); 
            if ($paramCount > 2) { 
                return $this->oaiError('badArgument', 'resumptionToken is an exclusive argument');
            }
            // Karena kita belum support token beneran, return error token invalid
            return $this->oaiError('badResumptionToken', 'Invalid resumptionToken');
        }

        // 5. Validasi MetadataPrefix (Wajib untuk List/Get kecuali ada token)
        if (in_array($verb, ['ListRecords', 'ListIdentifiers', 'GetRecord'])) {
            if (!$request->has('metadataPrefix')) {
                return $this->oaiError('badArgument', 'Missing metadataPrefix');
            }
            if ($request->input('metadataPrefix') !== 'oai_dc') {
                return $this->oaiError('cannotDisseminateFormat', 'Only oai_dc is supported');
            }
        }

        // 6. Validasi Tanggal (from & until)
        if ($request->has('from') || $request->has('until')) {
            try {
                if ($request->has('from')) {
                    Carbon::parse($request->input('from'));
                }
                if ($request->has('until')) {
                    Carbon::parse($request->input('until'));
                }
            } catch (\Exception $e) {
                // Jika Carbon gagal parse (misal inputnya "junk"), return badArgument
                return $this->oaiError('badArgument', 'Invalid date format');
            }
        }

        // 7. Validasi Identifier (Untuk GetRecord & ListMetadataFormats)
        if (in_array($verb, ['GetRecord', 'ListMetadataFormats'])) {
            if ($verb === 'GetRecord' && !$request->has('identifier')) {
                return $this->oaiError('badArgument', 'Missing identifier');
            }
            // ListMetadataFormats allows identifier to be optional.
        }

        // ===============================================
        // DISPATCHER
        // ===============================================
        switch ($verb) {
            case 'Identify':
                // Re-implement Identify Logic within Handle or call method? User wants handle method replacement.
                // Logic based on previous Identify method but simplified:
                $earliestDate = Submission::where('journal_id', $journal->id)
                    ->where('status', Submission::STATUS_PUBLISHED)
                    ->min('updated_at'); // Using updated_at as datestamp basis, or publication date? OAI usually datestamp.
                
                // Fallback to publication date if we need specifically that, but user prompt used 'updated_at' for records.
                $earliestDate = $earliestDate ? Carbon::parse($earliestDate) : now();

                return response()->view('journal.public.oai.identify', [
                    'journal' => $journal,
                    'earliestDate' => $earliestDate->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z'),
                    'baseUrl' => route('journal.oai', $journal->slug)
                ])->header('Content-Type', 'text/xml');

            case 'ListMetadataFormats':
                // Logic cek identifier existance jika ada param identifier
                if ($request->has('identifier')) {
                     $id = $this->extractId($request->input('identifier'));
                     if (!$id) {
                         return $this->oaiError('idDoesNotExist', 'Invalid identifier format');
                     }
                     // Support UUID & Slug
                     $exists = Submission::where('journal_id', $journal->id)
                        ->where(function($q) use ($id) {
                            $q->where('id', $id)->orWhere('slug', $id);
                        })->exists();

                     if (!$exists) {
                         return $this->oaiError('idDoesNotExist', 'The specified identifier does not exist');
                     }
                }
                return response()->view('journal.public.oai.metadata_formats', compact('journal'))
                    ->header('Content-Type', 'text/xml');

            case 'ListSets':
                 // Logic Sets Dummy
                 $sets = [
                    (object)['spec' => strtoupper($journal->abbreviation ?? 'JRN'), 'name' => $journal->name],
                    (object)['spec' => strtoupper($journal->abbreviation ?? 'JRN') . ':ART', 'name' => 'Articles']
                ];
                return response()->view('journal.public.oai.list_sets', compact('journal', 'sets'))
                    ->header('Content-Type', 'text/xml');

            case 'ListIdentifiers':
            case 'ListRecords':
                // Query Filter Tanggal
                $query = Submission::where('journal_id', $journal->id)
                    ->where('status', Submission::STATUS_PUBLISHED);
                
                // Use updated_at for OAI datestamp filtering
                if ($request->has('from')) {
                    $query->where('updated_at', '>=', Carbon::parse($request->input('from')));
                }
                if ($request->has('until')) {
                    $query->where('updated_at', '<=', Carbon::parse($request->input('until')));
                }
                
                // Eager Load Relations to prevent N+1 and ensure view has data
                $query->with(['publication', 'authors', 'issue', 'journal', 'galleys']);

                $records = $query->orderBy('updated_at', 'desc')->take(100)->get();

                if ($records->isEmpty()) {
                    return $this->oaiError('noRecordsMatch', 'No records found');
                }

                // If ListIdentifiers, we pass 'verb' => 'ListIdentifiers' if view needs it (Step 813 view logic used it?)
                // Step 841 view uses $url->current() and hardcoded verb in request tag. Good.
                $view = ($verb === 'ListIdentifiers') ? 'journal.public.oai.list_identifiers' : 'journal.public.oai.list_records';
                return response()->view($view, compact('records', 'journal', 'verb'))
                    ->header('Content-Type', 'text/xml');

            case 'GetRecord':
                $id = $this->extractId($request->input('identifier'));
                if (!$id) {
                     return $this->oaiError('idDoesNotExist', 'Invalid identifier format');
                }

                $recordRaw = Submission::where('journal_id', $journal->id)
                    ->where(function($q) use ($id) {
                        $q->where('id', $id)->orWhere('slug', $id);
                    })
                    ->where('status', Submission::STATUS_PUBLISHED)
                    ->with(['publication', 'authors', 'issue', 'journal', 'galleys']) // Eager load
                    ->first();

                if (!$recordRaw) {
                    return $this->oaiError('idDoesNotExist', 'Identifier not found');
                }
                
                // Naming consistency for view: $record or $submission?
                // Step 863 view uses $record.
                $record = $recordRaw;
                return response()->view('journal.public.oai.get_record', compact('record', 'journal'))
                    ->header('Content-Type', 'text/xml');
        }
    }

    // --- HELPER METHODS ---

    private function extractId($oaiString) {
        // format: oai:domain:article/ID or just ID?
        // Standard OAI Identifier: oai:archive:id
        if (!$oaiString) return null;
        $parts = explode('/', $oaiString);
        return end($parts);
    }

    private function oaiError($code, $message)
    {
        // 1. Ambil URL dasar tanpa parameter query
        $baseUrl = url()->current(); 

        // 2. Susun atribut request. HANYA masukkan yang aman/valid.
        // PENTING: Gunakan htmlspecialchars untuk mencegah XML broken (invalid"id)
        $requestAttributes = '';
        $verb = request('verb');
        if ($verb) {
            $requestAttributes .= ' verb="' . htmlspecialchars($verb, ENT_QUOTES) . '"';
        }
        
        // Hati-hati memasukkan identifier yang error ke atribut, lebih aman tidak dimasukkan
        // jika itu menyebabkan error parsing, tapi validator OAI kadang memintanya.
        // Kuncinya adalah htmlspecialchars(..., ENT_QUOTES).
        if (request('identifier')) {
            $requestAttributes .= ' identifier="' . htmlspecialchars(request('identifier'), ENT_QUOTES) . '"';
        }
        if (request('metadataPrefix')) {
             $requestAttributes .= ' metadataPrefix="' . htmlspecialchars(request('metadataPrefix'), ENT_QUOTES) . '"';
        }

        // 3. XML String Manual (Lebih aman daripada View Blade untuk error handling)
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <OAI-PMH xmlns="http://www.openarchives.org/OAI/2.0/" 
                xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
                xsi:schemaLocation="http://www.openarchives.org/OAI/2.0/ http://www.openarchives.org/OAI/2.0/OAI-PMH.xsd">
            <responseDate>' . now()->setTimezone('UTC')->format('Y-m-d\TH:i:s\Z') . '</responseDate>
            <request' . $requestAttributes . '>' . htmlspecialchars($baseUrl) . '</request>
            <error code="' . $code . '">' . htmlspecialchars($message) . '</error>
        </OAI-PMH>';

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }
}
