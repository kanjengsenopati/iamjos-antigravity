<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\Publication;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionAuthor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PublicationController extends Controller
{
    /**
     * Get publication data for the publication tab
     */
    public function show($journal, Submission $submission)
    {
        $publication = $submission->getOrCreatePublication();
        $publication->load(['authors', 'section', 'issue']);

        return response()->json([
            'publication' => $publication,
            'authors' => $publication->authors,
        ]);
    }

    /**
     * Update title and abstract
     */
    public function updateTitleAbstract(Request $request, $journal, Submission $submission)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'subtitle' => 'nullable|string|max:255',
            'abstract' => 'nullable|string|max:10000',
        ]);

        $publication = $submission->getOrCreatePublication();
        $publication->update($validated);

        // Also sync to submission for compatibility
        $submission->update([
            'title' => $validated['title'],
            'subtitle' => $validated['subtitle'] ?? null,
            'abstract' => $validated['abstract'] ?? null,
        ]);

        return back()->with('success', 'Title and abstract updated successfully.');
    }

    /**
     * Update metadata (keywords, etc.)
     */
    public function updateMetadata(Request $request, $journal, Submission $submission)
    {
        $validated = $request->validate([
            'keywords' => 'nullable|string|max:1000',
            'pages' => 'nullable|string|max:50',
            'url_path' => 'nullable|string|max:255',
        ]);

        $publication = $submission->getOrCreatePublication();
        $publication->update($validated);

        // Sync keywords to submission
        if (isset($validated['keywords'])) {
            $submission->update(['keywords' => $validated['keywords']]);
        }

        return back()->with('success', 'Metadata updated successfully.');
    }

    /**
     * Update license and DOI
     */
    public function updateLicense(Request $request, $journal, Submission $submission)
    {
        $validated = $request->validate([
            'doi' => 'nullable|string|max:255',
            'copyright_holder' => 'nullable|string|max:255',
            'copyright_year' => 'nullable|integer|min:1900|max:2100',
            'license_url' => 'nullable|url|max:500',
        ]);

        $publication = $submission->getOrCreatePublication();
        $publication->update($validated);

        return back()->with('success', 'License information updated successfully.');
    }

    /**
     * Store a new contributor
     */
    public function storeContributor(Request $request, $journal, Submission $submission)
    {
        $validated = $request->validate([
            'given_name' => 'required|string|max:100',
            'family_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'affiliation' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'orcid' => 'nullable|string|max:100',
            'is_corresponding' => 'boolean',
            'include_in_browse' => 'boolean',
            'user_group_id' => 'nullable|string|max:50',
        ]);

        $publication = $submission->getOrCreatePublication();

        // Get the next sort order
        $maxOrder = $publication->authors()->max('sort_order') ?? 0;

        $author = SubmissionAuthor::create([
            'submission_id' => $submission->id,
            'publication_id' => $publication->id,
            'name' => "{$validated['given_name']} {$validated['family_name']}",
            'given_name' => $validated['given_name'],
            'family_name' => $validated['family_name'],
            'email' => $validated['email'],
            'affiliation' => $validated['affiliation'] ?? null,
            'country' => $validated['country'] ?? null,
            'orcid' => $validated['orcid'] ?? null,
            'is_corresponding' => $validated['is_corresponding'] ?? false,
            'include_in_browse' => $validated['include_in_browse'] ?? true,
            'user_group_id' => $validated['user_group_id'] ?? 'author',
            'sort_order' => $maxOrder + 1,
        ]);

        return back()->with('success', 'Contributor added successfully.');
    }

    /**
     * Update a contributor
     */
    public function updateContributor(Request $request, $journal, Submission $submission, SubmissionAuthor $author)
    {
        $validated = $request->validate([
            'given_name' => 'required|string|max:100',
            'family_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'affiliation' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'orcid' => 'nullable|string|max:100',
            'is_corresponding' => 'boolean',
            'include_in_browse' => 'boolean',
            'user_group_id' => 'nullable|string|max:50',
        ]);

        $author->update([
            'name' => "{$validated['given_name']} {$validated['family_name']}",
            'given_name' => $validated['given_name'],
            'family_name' => $validated['family_name'],
            'email' => $validated['email'],
            'affiliation' => $validated['affiliation'] ?? null,
            'country' => $validated['country'] ?? null,
            'orcid' => $validated['orcid'] ?? null,
            'is_corresponding' => $validated['is_corresponding'] ?? false,
            'include_in_browse' => $validated['include_in_browse'] ?? true,
            'user_group_id' => $validated['user_group_id'] ?? $author->user_group_id,
        ]);

        return back()->with('success', 'Contributor updated successfully.');
    }

    /**
     * Delete a contributor
     */
    public function destroyContributor($journal, Submission $submission, SubmissionAuthor $author)
    {
        $author->delete();

        return back()->with('success', 'Contributor removed.');
    }

    /**
     * Reorder contributors
     */
    public function reorderContributors(Request $request, $journal, Submission $submission)
    {
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'uuid',
        ]);

        $publication = $submission->getOrCreatePublication();

        foreach ($request->order as $index => $authorId) {
            SubmissionAuthor::where('id', $authorId)
                ->where('publication_id', $publication->id)
                ->update(['sort_order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Assign to issue (scheduling)
     */
    public function assignIssue(Request $request, $journal, Submission $submission)
    {
        $validated = $request->validate([
            'issue_id' => 'required|uuid|exists:issues,id',
            'section_id' => 'nullable|uuid|exists:sections,id',
            'pages' => 'nullable|string|max:50',
            'date_published' => 'nullable|date',
        ]);

        // Security: Verify issue belongs to the current journal
        $currentJournal = current_journal();
        $issue = Issue::find($validated['issue_id']);

        if (!$issue || $issue->journal_id !== $currentJournal->id) {
            return back()->with('error', 'Invalid issue selected.');
        }

        // Security: Verify section belongs to the current journal (if provided)
        if (!empty($validated['section_id'])) {
            $section = \App\Models\Section::find($validated['section_id']);
            if (!$section || $section->journal_id !== $currentJournal->id) {
                return back()->with('error', 'Invalid section selected.');
            }
        }

        $publication = $submission->getOrCreatePublication();

        $publication->update([
            'issue_id' => $validated['issue_id'],
            'section_id' => $validated['section_id'] ?? $publication->section_id,
            'pages' => $validated['pages'] ?? null,
            'date_published' => $validated['date_published'] ?? null,
            'status' => Publication::STATUS_SCHEDULED,
        ]);

        // Sync to submission
        $submission->update([
            'issue_id' => $validated['issue_id'],
            'section_id' => $validated['section_id'] ?? $submission->section_id,
            'status' => Submission::STATUS_SCHEDULED,
        ]);

        return back()->with('success', "Scheduled for {$issue->identifier}");
    }

    /**
     * Unschedule from issue
     */
    public function unschedule($journal, Submission $submission)
    {
        $publication = $submission->getOrCreatePublication();

        $publication->update([
            'issue_id' => null,
            'status' => Publication::STATUS_QUEUED,
        ]);

        $submission->update(['issue_id' => null]);

        return back()->with('success', 'Publication unscheduled.');
    }

    /**
     * Publish the submission
     */
    public function publish(Request $request, $journal, Submission $submission)
    {
        $publication = $submission->getOrCreatePublication();

        // Validation
        if (!$publication->issue_id) {
            return back()->with('error', 'Please schedule to an issue first.');
        }

        $publication->update([
            'status' => Publication::STATUS_PUBLISHED,
            'date_published' => $publication->date_published ?? now(),
        ]);

        $submission->update([
            'status' => Submission::STATUS_PUBLISHED,
            'published_at' => $publication->date_published ?? now(),
        ]);

        return back()->with('success', 'Publication is now live!');
    }

    /**
     * Unpublish the submission
     */
    public function unpublish($journal, Submission $submission)
    {
        $publication = $submission->getOrCreatePublication();

        $publication->update([
            'status' => Publication::STATUS_UNPUBLISHED,
        ]);

        $submission->update([
            'status' => Submission::STATUS_ACCEPTED,
            'published_at' => null,
        ]);

        return back()->with('success', 'Publication has been unpublished.');
    }

    /**
     * Get sections for dropdown
     */
    public function getSections($journal)
    {
        $journalModel = current_journal();

        $sections = Section::where('journal_id', $journalModel->id)
            ->where('is_active', true)
            ->orderBy('seq')
            ->get(['id', 'title', 'abbreviation']);

        return response()->json($sections);
    }
}
