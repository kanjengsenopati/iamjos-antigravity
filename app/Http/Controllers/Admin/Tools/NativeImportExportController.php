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
            'xml_file' => 'required|file|mimes:xml,text|max:51200', // Max 50MB (increased for base64 files)
        ]);

        try {
            // Read content
            $content = file_get_contents($request->file('xml_file')->getRealPath());

            // Clean namespaces to make parsing easier (OJS uses strict namespaces like pkp:primary_contact)
            // This is a robust way to handle XML migration without complex namespace registry
            $content = str_replace(['<pkp:', '</pkp:'], ['<', '</'], $content);
            $xml = new SimpleXMLElement($content);

            $importedCount = 0;

            DB::beginTransaction();

            // 1. Normalize Input: Always treat as an array of articles
            $articles = [];
            if ($xml->getName() === 'article') {
                $articles[] = $xml; // Single article export
            } elseif ($xml->getName() === 'articles') {
                foreach ($xml->article as $node) {
                    $articles[] = $node;
                }
            } elseif (isset($xml->issue)) {
                // If importing issues, we iterate through issues then their articles
                foreach ($xml->issue as $issueNode) {
                    $issue = $this->importIssue($issueNode, $journal);
                    if (isset($issueNode->articles->article)) {
                        foreach ($issueNode->articles->article as $articleNode) {
                            $this->importArticleNode($articleNode, $journal, $issue); // Use new method
                            $importedCount++;
                        }
                    }
                }
                // If we processed issues, we might skip the dedicated article loop below unless there are mixed nodes
                // For safety/simplicity in this specific request context which focuses on <article> root:
                if (empty($articles) && $importedCount > 0) {
                    DB::commit();
                    return back()->with('success', "XML imported successfully. {$importedCount} articles processed from issues.");
                }
            }

            foreach ($articles as $articleNode) {
                $this->importArticleNode($articleNode, $journal);
                $importedCount++;
            }

            DB::commit();

            if ($importedCount === 0) {
                return back()->with('error', 'XML structure matched, but no articles were processed. Check if <publication> tag exists for OJS 3.3 exports.');
            }

            return back()->with('success', "ETL Success: Imported {$importedCount} articles from OJS 3.3 XML.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Native XML Import Error: ' . $e->getMessage());
            return back()->with('error', 'Failed to import XML: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')');
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
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found');
        }

        $ids = $request->input('submission_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one article to export.');
        }

        $submissions = Submission::whereIn('id', $ids)
            ->where('journal_id', $journal->id)
            ->with(['authors', 'issue', 'section', 'files'])
            ->get();

        $filename = 'articles_export_' . date('Y-m-d_His') . '.xml';

        return response()->streamDownload(function () use ($submissions, $journal) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><articles/>');
            $xml->addAttribute('xmlns', 'http://iamjos.org/native');
            $xml->addAttribute('journal', $journal->name);
            $xml->addAttribute('exported_at', now()->toIso8601String());

            foreach ($submissions as $submission) {
                $this->appendArticleNode($xml, $submission);
            }

            echo $xml->asXML();
        }, $filename, [
            'Content-Type' => 'application/xml',
        ]);
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
