<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Submission;
use App\Models\SubmissionAuthor;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\RedirectResponse;

class NativeImportExportController extends Controller
{
    /**
     * Display the Native XML Plugin page.
     */
    public function index()
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found');
        }

        // Fetch data for export tabs
        $submissions = Submission::where('journal_id', $journal->id)
            ->whereNotNull('submitted_at')
            ->with(['authors', 'issue', 'section'])
            ->latest('submitted_at')
            ->get();

        $issues = Issue::where('journal_id', $journal->id)
            ->withCount('submissions')
            ->latest()
            ->get();

        return view('manager.tools.importexport.native', compact('journal', 'submissions', 'issues'));
    }

    /**
     * Import XML file.
     */
    public function import(Request $request)
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found');
        }

        $request->validate([
            'xml_file' => 'required|file|mimes:xml,text|max:51200', // Max 50MB
        ]);

        try {
            $content = file_get_contents($request->file('xml_file')->getRealPath());

            // 1. Clean XML Namespaces (Crucial for parsing OJS exports easily)
            $content = str_replace(['<pkp:', '</pkp:'], ['<', '</'], $content);
            $xml = new SimpleXMLElement($content);

            DB::beginTransaction();
            $processedCount = 0;

            // Normalize to array (Handle Single vs Multiple Articles)
            $articles = ($xml->getName() === 'article') ? [$xml] : $xml->article;

            foreach ($articles as $articleNode) {
                // Get the main publication node (Metadata)
                $publication = $articleNode->publication;
                if (!$publication) continue;

                // --- A. HANDLE USER & AUTHOR (The Submitter) ---
                $primaryAuthorNode = null;
                $authorsData = [];
                $authorSequence = 0;

                // Iterate authors to find primary contact and prepare data
                if (isset($publication->authors->author)) {
                    foreach ($publication->authors->author as $authNode) {
                        $isPrimary = (string) $authNode['primary_contact'] === 'true';
                        $email = (string) $authNode->email;
                        if (empty($email)) $email = 'no-email-' . uniqid() . '@example.com';

                        // Try to find existing user by email to link user_id
                        $existingUser = \App\Models\User::where('email', $email)->first();

                        $authorData = [
                            'user_id' => $existingUser?->id,
                            'given_name' => (string) $authNode->givenname,
                            'family_name' => (string) ($authNode->familyname ?? ''),
                            'email' => $email,
                            'affiliation' => (string) $authNode->affiliation,
                            'country' => (string) $authNode->country,
                            'orcid' => (string) $authNode->orcid,
                            'biography' => strip_tags((string) $authNode->biography),
                            'is_primary_contact' => $isPrimary,
                            'is_corresponding' => $isPrimary, // Assuming primary is corresponding
                            'include_in_browse' => (string) ($authNode['include_in_browse'] ?? 'true') === 'true',
                            'sort_order' => $authorSequence++,
                        ];

                        $authorsData[] = $authorData;

                        if ($isPrimary) {
                            $primaryAuthorNode = $authorData;
                        }
                    }
                }

                // Fallback: If no primary contact marked, take the first author
                if (!$primaryAuthorNode && count($authorsData) > 0) {
                    $primaryAuthorNode = $authorsData[0];
                    $authorsData[0]['is_primary_contact'] = true;
                    $authorsData[0]['is_corresponding'] = true;
                }

                // Find or Create the User (Submitter)
                $submitterUser = null;
                if ($primaryAuthorNode) {
                    $submitterUser = \App\Models\User::where('email', $primaryAuthorNode['email'])->first();
                    
                    if (!$submitterUser) {
                        $submitterUser = \App\Models\User::create([
                            'name' => trim($primaryAuthorNode['given_name'] . ' ' . $primaryAuthorNode['family_name']),
                            'given_name' => $primaryAuthorNode['given_name'],
                            'family_name' => $primaryAuthorNode['family_name'],
                            'email' => $primaryAuthorNode['email'],
                            'password' => bcrypt('password'), // Default password
                            'username' => explode('@', $primaryAuthorNode['email'])[0] . rand(100, 999),
                            'affiliation' => $primaryAuthorNode['affiliation'],
                            'country' => $primaryAuthorNode['country'],
                            'orcid_id' => $primaryAuthorNode['orcid'],
                            'bio' => $primaryAuthorNode['biography'],
                        ]);
                    }

                    // Update author data with created user_id
                    foreach ($authorsData as &$aData) {
                        if ($aData['email'] === $submitterUser->email) {
                            $aData['user_id'] = $submitterUser->id;
                        }
                    }
                }

                // --- B. CREATE SUBMISSION ---
                $dateSubmitted = (string) ($articleNode['date_submitted'] ?? now());

                $submission = Submission::create([
                    'journal_id' => $journal->id,
                    'user_id' => $submitterUser ? $submitterUser->id : \Illuminate\Support\Facades\Auth::id(), // Link to actual submitter
                    'section_id' => $journal->sections->first()->id ?? null,
                    'title' => (string) $publication->title,
                    'subtitle' => (string) ($publication->subtitle ?? null),
                    'abstract' => strip_tags((string) $publication->abstract),
                    'status' => Submission::STATUS_PUBLISHED, // Import as published to retain history
                    'stage' => Submission::STAGE_PRODUCTION,
                    'submitted_at' => $dateSubmitted,
                    'published_at' => $dateSubmitted, // Set published date
                    'language' => (string) ($publication['locale'] ?? 'en'),
                ]);

                // --- C. SAVE AUTHORS (Link to Submission) ---
                foreach ($authorsData as $auth) {
                    $fullName = trim($auth['given_name'] . ' ' . $auth['family_name']);
                    
                    SubmissionAuthor::create([
                        'submission_id' => $submission->id,
                        'user_id' => $auth['user_id'],
                        'name' => $fullName,
                        'given_name' => $auth['given_name'],
                        'family_name' => $auth['family_name'],
                        'first_name' => $auth['given_name'], // Redundant but requested
                        'last_name' => $auth['family_name'], // Redundant but requested
                        'preferred_public_name' => $fullName,
                        'email' => $auth['email'],
                        'affiliation' => $auth['affiliation'],
                        'country' => $auth['country'],
                        'orcid' => $auth['orcid'],
                        'biography' => $auth['biography'],
                        'is_primary_contact' => $auth['is_primary_contact'],
                        'is_corresponding' => $auth['is_corresponding'],
                        'include_in_browse' => $auth['include_in_browse'],
                        'sort_order' => $auth['sort_order'],
                    ]);
                }

                // --- D. HANDLE REFERENCES (Citations) ---
                if (isset($publication->citations->citation)) {
                    $citationsList = [];
                    foreach ($publication->citations->citation as $cite) {
                        $citationsList[] = trim((string) $cite);
                    }
                    // Save as text block in 'references' column
                    $submission->references = implode("\n", $citationsList);
                    $submission->save();
                }

                // --- E. HANDLE FILES (Fixing 404s) ---
                if (isset($articleNode->submission_file)) {
                    foreach ($articleNode->submission_file as $fileNode) {
                        if (isset($fileNode->file->embed)) {
                            $base64 = (string) $fileNode->file->embed;
                            $originalFilename = (string) ($fileNode->name ?? 'file-' . uniqid() . '.pdf');
                            $fileContent = base64_decode($base64);

                            if ($fileContent) {
                                // Standardized Path: journals/{id}/submissions/{sub_id}/{filename}
                                // Ensure filename is clean
                                $cleanFilename = \Illuminate\Support\Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME))
                                    . '.' . pathinfo($originalFilename, PATHINFO_EXTENSION);

                                $storagePath = "journals/{$journal->id}/submissions/{$submission->id}/{$cleanFilename}";

                                // Store in 'public' disk
                                \Illuminate\Support\Facades\Storage::disk('public')->put($storagePath, $fileContent);

                                // Create Database Record for File (SubmissionFile)
                                // IMPORTANT: 'path' must be relative to storage/app/public
                                SubmissionFile::create([
                                    'submission_id' => $submission->id,
                                    'uploaded_by' => $submission->user_id,
                                    'file_path' => $storagePath,
                                    'file_name' => $originalFilename,
                                    'file_type' => SubmissionFile::TYPE_MANUSCRIPT, // Default type
                                    'mime_type' => 'application/pdf', // Or detect mime type
                                    'file_size' => strlen($fileContent),
                                    'version' => 1,
                                    'stage' => SubmissionFile::STAGE_SUBMISSION,
                                    // 'genre' => (string) $fileNode['genre'] ?? 'Article Text', // if genre supported
                                ]);
                            }
                        }
                    }
                }

                $processedCount++;
            }

            DB::commit();

            if ($processedCount === 0) {
                return back()->with('warning', 'XML parsed but no articles found. Check if root tag is <article> or <articles>.');
            }

            return back()->with('success', "Successfully imported $processedCount articles, users, and files.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Native XML Import Error: ' . $e->getMessage());
            return back()->with('error', 'Import Failed: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')');
        }
    }

    /**
     * Import a single article node (OJS 3.3 or Native format)
     */
    private function importArticleNode(SimpleXMLElement $articleNode, $journal, ?Issue $issue = null)
    {
        // 2. OJS 3.3 Logic: Metadata is inside <publication>
        // We take the latest publication version (usually the last one or the current one)
        $publication = $articleNode->publication ?? null;

        // If no publication node, try native format fallback (root level fields)
        if (!$publication) {
            // Attempt to use existing native import logic if it matches native structure
            if (isset($articleNode->title)) {
                return $this->importArticle($articleNode, $journal, $issue);
            }
            return; // Skip if neither OJS 3.3 nor Native format
        }

        // 3. Extract Metadata from OJS 3.3 <publication>
        $title = (string) $publication->title;
        $abstract = (string) $publication->abstract; // Note: OJS abstract contains HTML tags
        $dateSubmitted = (string) ($articleNode['date_submitted'] ?? now());
        $status = Submission::STATUS_SUBMITTED; // Default
        
        // Map OJS Status if available (stage_id)
        if (isset($articleNode['stage']) && (string)$articleNode['stage'] === 'production') {
            $status = Submission::STATUS_PUBLISHED;
        }

        // Create Submission
        $submission = Submission::create([
            'journal_id' => $journal->id,
            'issue_id' => $issue?->id,
            'user_id' => auth()->id() ?? 1, // Fallback to admin/system if no auth
            'title' => $title,
            'abstract' => strip_tags($abstract), // Clean HTML for consistency
            'status' => $status,
            'stage' => Submission::STAGE_SUBMISSION,
            'submitted_at' => $dateSubmitted,
            'section_id' => $journal->sections->first()->id ?? null,
            'metadata' => ['locale' => (string) ($publication['locale'] ?? 'en')],
        ]);

        // 4. Extract Authors
        if (isset($publication->authors->author)) {
            $seq = 0;
            foreach ($publication->authors->author as $authorNode) {
                $email = (string) $authorNode->email;
                if (empty($email)) $email = 'no-email-' . uniqid() . '@example.com'; // OJS sometimes allows no email for old records

                SubmissionAuthor::create([
                    'submission_id' => $submission->id,
                    'given_name' => (string) $authorNode->givenname,
                    'family_name' => (string) ($authorNode->familyname ?? ''),
                    'email' => $email,
                    'affiliation' => (string) $authorNode->affiliation,
                    'is_primary' => (string) ($authorNode['primary_contact'] ?? 'false') === 'true',
                    'seq' => $seq++,
                ]);
            }
        }

        // 5. Extract Files (Base64)
        // OJS Structure: <submission_file> -> <file> -> <embed>
        if (isset($articleNode->submission_file)) {
            foreach ($articleNode->submission_file as $fileNode) {
                if (isset($fileNode->file->embed)) {
                    $base64 = (string) $fileNode->file->embed;
                    $filename = (string) ($fileNode->name ?? 'file-' . uniqid() . '.pdf');
                    
                    // Decode
                    $fileContent = base64_decode($base64);

                    if ($fileContent) {
                        // Generate path: journals/{id}/submissions/{sub_id}/{filename}
                        $storagePath = "journals/{$journal->id}/submissions/{$submission->id}/" . \Illuminate\Support\Str::slug(pathinfo($filename, PATHINFO_FILENAME)) . '.' . pathinfo($filename, PATHINFO_EXTENSION);
                        
                        // Save to disk
                        \Illuminate\Support\Facades\Storage::disk('public')->put($storagePath, $fileContent);

                        // Determine file type/stage mapping
                        $fileStage = \App\Models\SubmissionFile::STAGE_SUBMISSION;
                        $fileType = \App\Models\SubmissionFile::TYPE_MANUSCRIPT;
                        
                        if (isset($fileNode['file_stage'])) {
                            // Map OJS file stages if needed, simplified here
                            if ((int)$fileNode['file_stage'] === 10) $fileStage = \App\Models\SubmissionFile::STAGE_PRODUCTION_READY; // Galley
                        }

                        // Save record
                        \App\Models\SubmissionFile::create([
                            'submission_id' => $submission->id,
                            'uploaded_by' => auth()->id() ?? 1,
                            'file_path' => $storagePath,
                            'file_name' => $filename,
                            'file_type' => $fileType,
                            'mime_type' => 'application/pdf', // Best guess for OJS exports usually PDF
                            'file_size' => strlen($fileContent),
                            'version' => 1,
                            'stage' => $fileStage,
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Export selected articles as XML.
     */
    public function exportArticles(Request $request): StreamedResponse|RedirectResponse
    {
        // 1. Setup & Data Fetching
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found');
        }

        $ids = $request->input('submission_ids', []);
        
        if (empty($ids)) {
            return back()->with('error', 'Please select at least one article to export.');
        }
        
        // Eager load relationships needed for the XML
        // Ensure we load 'files' and 'authors'
        $submissions = \App\Models\Submission::whereIn('id', $ids)
            ->where('journal_id', $journal->id)
            ->with(['authors', 'files', 'issue']) 
            ->get();

        if ($submissions->isEmpty()) {
            return back()->with('error', 'No articles found.');
        }

        // 2. Generate XML Filename
        $filename = 'native-' . date('Ymd-His') . '-articles.xml';

        // 3. Stream Download (To handle large Base64 strings efficiently)
        return response()->streamDownload(function () use ($submissions) {
            // Render the Blade View as XML string
            echo view('admin.tools.importexport.xml_export', compact('submissions'))->render();
        }, $filename, ['Content-Type' => 'text/xml']);
    }

    /**
     * Export selected issues as XML (including nested articles).
     */
    public function exportIssues(Request $request): StreamedResponse|RedirectResponse
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found');
        }

        $ids = $request->input('issue_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one issue to export.');
        }

        $issues = Issue::whereIn('id', $ids)
            ->where('journal_id', $journal->id)
            ->with(['submissions.authors', 'submissions.section'])
            ->get();

        $filename = 'issues_export_' . date('Y-m-d_His') . '.xml';

        return response()->streamDownload(function () use ($issues, $journal) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><issues/>');
            $xml->addAttribute('xmlns', 'http://iamjos.org/native');
            $xml->addAttribute('journal', $journal->name);
            $xml->addAttribute('exported_at', now()->toIso8601String());

            foreach ($issues as $issue) {
                $issueNode = $xml->addChild('issue');

                // Issue identification
                $identification = $issueNode->addChild('identification');
                $identification->addChild('volume', (string) $issue->volume);
                $identification->addChild('number', (string) $issue->number);
                $identification->addChild('year', (string) $issue->year);
                $identification->addChild('title', $this->escapeXml($issue->title ?? ''));
                $identification->addChild('url_path', $this->escapeXml($issue->url_path ?? ''));

                // Issue metadata
                $issueNode->addChild('description', $this->escapeXml($issue->description ?? ''));
                $issueNode->addChild('is_published', $issue->is_published ? 'true' : 'false');
                $issueNode->addChild('published_at', $issue->published_at?->toIso8601String() ?? '');

                // Nested articles
                $articlesNode = $issueNode->addChild('articles');
                foreach ($issue->submissions as $submission) {
                    $this->appendArticleNode($articlesNode, $submission);
                }
            }

            echo $xml->asXML();
        }, $filename, [
            'Content-Type' => 'application/xml',
        ]);
    }

    /**
     * Helper: Append article node to XML.
     */
    private function appendArticleNode(SimpleXMLElement $parent, Submission $submission): void
    {
        $article = $parent->addChild('article');
        $article->addAttribute('id', $submission->id);
        $article->addAttribute('status', $submission->status);

        // Basic metadata
        $article->addChild('submission_code', $this->escapeXml($submission->submission_code ?? ''));
        $article->addChild('title', $this->escapeXml($submission->title));
        $article->addChild('subtitle', $this->escapeXml($submission->subtitle ?? ''));
        $article->addChild('abstract', $this->escapeXml($submission->abstract ?? ''));
        $article->addChild('keywords', $this->escapeXml($submission->keywords ?? ''));
        $article->addChild('references', $this->escapeXml($submission->references ?? ''));

        // Section
        if ($submission->section) {
            $article->addChild('section', $this->escapeXml($submission->section->name));
        }

        // Dates
        $datesNode = $article->addChild('dates');
        $datesNode->addChild('submitted_at', $submission->submitted_at?->toIso8601String() ?? '');
        $datesNode->addChild('accepted_at', $submission->accepted_at?->toIso8601String() ?? '');
        $datesNode->addChild('published_at', $submission->published_at?->toIso8601String() ?? '');

        // Authors
        $authorsNode = $article->addChild('authors');
        foreach ($submission->authors as $author) {
            $authorNode = $authorsNode->addChild('author');
            $authorNode->addChild('given_name', $this->escapeXml($author->given_name ?? ''));
            $authorNode->addChild('family_name', $this->escapeXml($author->family_name ?? ''));
            $authorNode->addChild('email', $this->escapeXml($author->email ?? ''));
            $authorNode->addChild('affiliation', $this->escapeXml($author->affiliation ?? ''));
            $authorNode->addChild('orcid', $this->escapeXml($author->orcid ?? ''));
            $authorNode->addChild('is_primary', $author->is_primary ? 'true' : 'false');
        }
    }

    /**
     * Helper: Import article from XML node.
     */
    private function importArticle(SimpleXMLElement $node, $journal, ?Issue $issue = null): Submission
    {
        $submission = Submission::create([
            'journal_id' => $journal->id,
            'issue_id' => $issue?->id,
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'title' => (string) $node->title,
            'subtitle' => (string) ($node->subtitle ?? ''),
            'abstract' => (string) ($node->abstract ?? ''),
            'keywords' => (string) ($node->keywords ?? ''),
            'references' => (string) ($node->references ?? ''),
            'status' => (string) ($node->attributes()->status ?? Submission::STATUS_DRAFT),
            'stage' => Submission::STAGE_SUBMISSION,
        ]);

        // Import authors
        if (isset($node->authors->author)) {
            $seq = 0;
            foreach ($node->authors->author as $authorNode) {
                SubmissionAuthor::create([
                    'submission_id' => $submission->id,
                    'given_name' => (string) ($authorNode->given_name ?? $authorNode->firstname ?? ''),
                    'family_name' => (string) ($authorNode->family_name ?? $authorNode->lastname ?? ''),
                    'email' => (string) ($authorNode->email ?? ''),
                    'affiliation' => (string) ($authorNode->affiliation ?? ''),
                    'orcid' => (string) ($authorNode->orcid ?? ''),
                    'is_primary' => $seq === 0,
                    'seq' => $seq++,
                ]);
            }
        }

        return $submission;
    }

    /**
     * Helper: Import issue from XML node.
     */
    private function importIssue(SimpleXMLElement $node, $journal): Issue
    {
        $identification = $node->identification ?? $node;

        return Issue::create([
            'journal_id' => $journal->id,
            'volume' => (int) ($identification->volume ?? 1),
            'number' => (int) ($identification->number ?? 1),
            'year' => (int) ($identification->year ?? date('Y')),
            'title' => (string) ($identification->title ?? ''),
            'url_path' => (string) ($identification->url_path ?? ''),
            'description' => (string) ($node->description ?? ''),
            'is_published' => ((string) ($node->is_published ?? 'false')) === 'true',
        ]);
    }

    /**
     * Helper: Escape special characters for XML.
     */
    private function escapeXml(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
