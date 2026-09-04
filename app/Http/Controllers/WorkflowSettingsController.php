<?php

namespace App\Http\Controllers;

use App\Models\Journal;
use App\Models\LibraryFile;
use App\Models\ReviewForm;
use App\Models\SubmissionChecklist;
use App\Models\EmailTemplate;
use App\Services\ReviewFormTemplateService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class WorkflowSettingsController extends Controller
{
    // =====================================================
    // INDEX - Display All Workflow Settings
    // =====================================================

    /**
     * Display the workflow settings page with all data.
     */
    public function index(): View
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found.');
        }

        // Load all related data
        $checklists = $journal->submissionChecklists()->ordered()->get();
        $reviewForms = $journal->reviewForms()->get();
        $libraryFiles = $journal->libraryFiles()->latest()->get();
        $emailTemplates = $journal->emailTemplates()->orderBy('key')->get();

        // If no email templates exist, seed them
        if ($emailTemplates->isEmpty()) {
            EmailTemplate::seedForJournal($journal->id);
            $emailTemplates = $journal->emailTemplates()->orderBy('key')->get();
        }

        // Load Notification Templates (Per-Journal Logic)
        $defaults = \App\Services\WaGateway::getDefaultTemplates();
        $variables = \App\Services\WaGateway::getTemplateVariables();
        
        // Fetch existing overrides for this journal
        $journalTemplates = \App\Models\NotificationTemplate::where('channel', 'whatsapp')
            ->where('journal_id', $journal->id)
            ->get()
            ->keyBy('event_key');
            
        // Fetch global templates (for fallback if needed)
        $globalTemplates = \App\Models\NotificationTemplate::where('channel', 'whatsapp')
            ->whereNull('journal_id')
            ->get()
            ->keyBy('event_key');

        $notificationTemplates = [];

        foreach ($defaults as $key => $defaultBody) {
            // Determine effective body and status
            $templateObj = new \stdClass();
            $templateObj->event_key = $key;
            $templateObj->variables = $variables[$key] ?? [];
            
            // Priority 1: Journal Override
            if ($journalTemplates->has($key)) {
                $t = $journalTemplates->get($key);
                $templateObj->id = $t->id; 
                $templateObj->body = $t->body;
                $templateObj->is_active = $t->is_active;
                $templateObj->source = 'journal';
            } 
            // Priority 2: Global Configuration
            elseif ($globalTemplates->has($key)) {
                $t = $globalTemplates->get($key);
                $templateObj->id = null; // Mark as null so UI knows it's inherited
                $templateObj->body = $t->body;
                $templateObj->is_active = $t->is_active;
                $templateObj->source = 'global';
            } 
            // Priority 3: Hardcoded Default
            else {
                $templateObj->id = null;
                $templateObj->body = $defaultBody;
                $templateObj->is_active = true; // Default to active
                $templateObj->source = 'default';
            }
            
            $notificationTemplates[] = $templateObj;
        }

        return view('admin.journals.workflow', compact(
            'journal',
            'checklists',
            'reviewForms',
            'libraryFiles',
            'emailTemplates',
            'notificationTemplates'
        ));
    }

    // =====================================================
    // UPDATE SETTINGS - General Workflow Settings
    // =====================================================

    /**
     * Update general workflow settings (Author Guidelines, Review Defaults, Email Config).
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found.');
        }

        $tab = $request->input('tab', 'submissions');

        if ($tab === 'submissions') {
            $validated = $request->validate([
                'author_guidelines' => 'nullable',
                'metadata_keywords' => 'boolean',
                'metadata_references' => 'boolean',
                'metadata_languages' => 'boolean',
                'metadata_rights' => 'boolean',
                'metadata_coverage' => 'boolean',
                'metadata_disciplines' => 'boolean',
            ]);

            $journal->update([
                'author_guidelines' => $validated['author_guidelines'] ?? null,
                'submission_metadata_settings' => [
                    'keywords' => $request->boolean('metadata_keywords'),
                    'references' => $request->boolean('metadata_references'),
                    'languages' => $request->boolean('metadata_languages'),
                    'rights' => $request->boolean('metadata_rights'),
                    'coverage' => $request->boolean('metadata_coverage'),
                    'disciplines' => $request->boolean('metadata_disciplines'),
                ],
            ]);

            return redirect()->route('journal.settings.workflow.index', ['journal' => $journal->slug, 'tab' => 'submissions'])->with('success', 'Submission settings saved successfully.');
        }

        if ($tab === 'review') {
            $validated = $request->validate([
                'review_mode' => 'required|in:double_blind,blind,open',
                'review_response_weeks' => 'required|integer|min:1|max:12',
                'review_completion_weeks' => 'required|integer|min:1|max:24',
                'reviewer_guidelines' => 'nullable|string',
                'require_competing_interests' => 'boolean',
            ]);

            $journal->update([
                'review_mode' => $validated['review_mode'],
                'review_response_weeks' => $validated['review_response_weeks'],
                'review_completion_weeks' => $validated['review_completion_weeks'],
                'reviewer_guidelines' => $validated['reviewer_guidelines'] ?? null,
                'require_competing_interests' => $request->boolean('require_competing_interests'),
            ]);

            $validReviewSubtabs = ['setup', 'guidance', 'forms'];
            $subtab = $request->input('subtab', 'setup');
            if (!in_array($subtab, $validReviewSubtabs, true)) {
                $subtab = 'setup';
            }

            return redirect()->route('journal.settings.workflow.index', [
                'journal' => $journal->slug,
                'tab' => 'review',
                'subtab' => $subtab,
            ])->with('success', 'Review settings saved successfully.')
              ->with('review_subtab', $subtab);
        }

        if ($tab === 'emails') {
            $validated = $request->validate([
                'email_signature' => 'nullable|string',
                'email_bounce_address' => 'nullable|email',
                'email_reply_to' => 'nullable|email',
            ]);

            $journal->update([
                'email_signature' => $validated['email_signature'] ?? null,
                'email_bounce_address' => $validated['email_bounce_address'] ?? null,
                'email_reply_to' => $validated['email_reply_to'] ?? null,
            ]);

            return redirect()->route('journal.settings.workflow.index', ['journal' => $journal->slug, 'tab' => 'emails'])->with('success', 'Email settings saved successfully.');
        }

        return redirect()->route('journal.settings.workflow.index', ['journal' => $journal->slug, 'tab' => $tab])->with('error', 'Unknown settings tab.');
    }

    // =====================================================
    // SUBMISSION CHECKLIST CRUD
    // =====================================================

    /**
     * Store a new submission checklist item.
     */
    public function storeChecklist(Request $request): RedirectResponse
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'is_required' => 'boolean',
        ]);

        $maxOrder = SubmissionChecklist::where('journal_id', $journal->id)->max('sort_order') ?? 0;

        SubmissionChecklist::create([
            'journal_id' => $journal->id,
            'content' => $validated['content'],
            'is_required' => $request->boolean('is_required'),
            'sort_order' => $maxOrder + 1,
            'is_active' => true,
        ]);

        return back()->with('success', 'Checklist item added successfully.');
    }

    /**
     * Update a submission checklist item.
     */
    public function updateChecklist(Request $request, string $journal, string $checklistId): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $checklist = SubmissionChecklist::findOrFail($checklistId);

        if ($checklist->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'is_required' => 'boolean',
        ]);

        $checklist->update([
            'content' => $validated['content'],
            'is_required' => $request->boolean('is_required'),
        ]);

        return back()->with('success', 'Checklist item updated successfully.');
    }

    /**
     * Delete a submission checklist item.
     */
    public function destroyChecklist(string $journal, string $checklistId): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $checklist = SubmissionChecklist::findOrFail($checklistId);

        if ($checklist->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        $checklist->delete();

        return back()->with('success', 'Checklist item deleted successfully.');
    }

    // =====================================================
    // REVIEW FORMS CRUD
    // =====================================================

    /**
     * Store a new review form.
     */
    public function storeReviewForm(Request $request): RedirectResponse
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        ReviewForm::create([
            'journal_id' => $journal->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()->route('journal.settings.workflow.index', [
            'journal' => $journal->slug,
            'tab' => 'review',
            'subtab' => 'forms',
        ])->with('success', 'Review form created successfully.')
          ->with('review_subtab', 'forms');
    }

    /**
     * Update a review form.
     */
    public function updateReviewForm(Request $request, string $journal, string $reviewFormId): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $reviewForm = ReviewForm::findOrFail($reviewFormId);

        if ($reviewForm->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $reviewForm->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('journal.settings.workflow.index', [
            'journal' => $currentJournal->slug,
            'tab' => 'review',
            'subtab' => 'forms',
        ])->with('success', 'Review form updated successfully.')
          ->with('review_subtab', 'forms');
    }

    /**
     * Delete a review form.
     */
    public function destroyReviewForm(string $journal, string $reviewFormId): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $reviewForm = ReviewForm::findOrFail($reviewFormId);

        if ($reviewForm->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        if ($reviewForm->response_count > 0) {
            return redirect()->route('journal.settings.workflow.index', [
                'journal' => $currentJournal->slug,
                'tab' => 'review',
                'subtab' => 'forms',
            ])->with('error', 'Cannot delete review form with existing responses.')
              ->with('review_subtab', 'forms');
        }

        $reviewForm->delete();

        return redirect()->route('journal.settings.workflow.index', [
            'journal' => $currentJournal->slug,
            'tab' => 'review',
            'subtab' => 'forms',
        ])->with('success', 'Review form deleted successfully.')
          ->with('review_subtab', 'forms');
    }

    /**
     * Duplicate a review form.
     */
    public function duplicateReviewForm(string $journal, string $reviewFormId): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $reviewForm = ReviewForm::findOrFail($reviewFormId);

        if ($reviewForm->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        $newForm = $reviewForm->duplicate();

        return redirect()->route('journal.settings.workflow.index', [
            'journal' => $currentJournal->slug,
            'tab' => 'review',
            'subtab' => 'forms',
        ])->with('success', "Review form duplicated successfully: {$newForm->title}")
          ->with('review_subtab', 'forms');
    }

    // =====================================================
    // REVIEW FORM TEMPLATES (Phase 4)
    // =====================================================

    /**
     * Show available review form templates.
     */
    public function showTemplates(): View
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found.');
        }

        $templates = ReviewFormTemplateService::getTemplates();
        $isId = app()->getLocale() === 'id';

        return view('admin.journals.review-forms.templates', compact('journal', 'templates', 'isId'));
    }

    /**
     * Create a review form from a template.
     */
    public function createFromTemplate(Request $request, string $journal): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $validated = $request->validate([
            'template_key' => 'required|string',
            'locale' => 'nullable|string|in:en,id',
        ]);

        try {
            $locale = $validated['locale'] ?? app()->getLocale();
            $form = ReviewFormTemplateService::createFromTemplate(
                $currentJournal,
                $validated['template_key'],
                $locale
            );

            return redirect()
                ->route('journal.settings.workflow.review-forms.builder', [
                    'journal' => $currentJournal->slug,
                    'reviewForm' => $form->id
                ])
                ->with('success', 'Review form created successfully from template.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Export a review form to JSON.
     */
    public function exportForm(string $journal, string $reviewFormId)
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $reviewForm = ReviewForm::findOrFail($reviewFormId);

        if ($reviewForm->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        $data = ReviewFormTemplateService::exportToJson($reviewForm);

        $filename = \Illuminate\Support\Str::slug($reviewForm->title) . '-form-template.json';

        return response()->json($data)
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Content-Type', 'application/json');
    }

    /**
     * Import a review form from JSON.
     */
    public function importForm(Request $request, string $journal): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $validated = $request->validate([
            'import_file' => 'required|file|mimes:json|max:5120', // 5MB max
        ]);

        try {
            $file = $request->file('import_file');
            $jsonContent = file_get_contents($file->getRealPath());
            $data = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON file format.');
            }

            $form = ReviewFormTemplateService::importFromJson($currentJournal, $data);

            return redirect()
                ->route('journal.settings.workflow.review-forms.builder', [
                    'journal' => $currentJournal->slug,
                    'reviewForm' => $form->id
                ])
                ->with('success', 'Review form imported successfully. You can activate it when ready.');
        } catch (\Exception $e) {
            return back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }

    // =====================================================
    // LIBRARY FILES
    // =====================================================

    /**
     * Upload a library file.
     */
    public function storeLibraryFile(Request $request): RedirectResponse
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found.');
        }

        $validated = $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx|max:10240', // 10MB max
            'category' => 'nullable|string|in:marketing,permissions,contracts,general',
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $fileSize = $file->getSize();

        // Store file
        $path = $file->store("journals/{$journal->id}/library", 'public');

        LibraryFile::create([
            'journal_id' => $journal->id,
            'name' => pathinfo($originalName, PATHINFO_FILENAME),
            'original_name' => $originalName,
            'file_path' => $path,
            'file_type' => strtoupper($extension),
            'category' => $validated['category'] ?? 'general',
            'file_size' => $fileSize,
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', 'File uploaded successfully.');
    }

    /**
     * Download a library file.
     */
    public function downloadLibraryFile(string $journal, string $libraryFileId)
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $libraryFile = LibraryFile::findOrFail($libraryFileId);

        if ($libraryFile->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        if (!Storage::disk('public')->exists($libraryFile->file_path)) {
            return back()->with('error', 'File not found.');
        }

        return Storage::disk('public')->download($libraryFile->file_path, $libraryFile->original_name);
    }

    /**
     * Delete a library file.
     */
    public function destroyLibraryFile(string $journal, string $libraryFileId): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $libraryFile = LibraryFile::findOrFail($libraryFileId);

        if ($libraryFile->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        $libraryFile->delete(); // This will also delete the file from storage via model boot

        return back()->with('success', 'File deleted successfully.');
    }

    // =====================================================
    // EMAIL TEMPLATES
    // =====================================================

    /**
     * Update an email template.
     */
    /**
     * Update an email template.
     */
    public function updateEmailTemplate(Request $request, string $journal, string $emailTemplateId): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $emailTemplate = EmailTemplate::findOrFail($emailTemplateId);

        if ($emailTemplate->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        $emailTemplate->update([
            'subject' => $validated['subject'],
            'body' => $validated['body'],
            'is_custom' => true,
        ]);

        return back()->with('success', 'Email template updated successfully.')->with('email_subtab', 'templates');
    }

    /**
     * Toggle email template enabled status.
     */
    public function toggleEmailTemplate(Request $request, string $journal, string $emailTemplateId)
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Journal not found'], 404);
            }
            abort(404, 'Journal not found.');
        }

        $emailTemplate = EmailTemplate::findOrFail($emailTemplateId);

        if ($emailTemplate->journal_id !== $currentJournal->id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Unauthorized.');
        }

        $emailTemplate->update([
            'is_enabled' => !$emailTemplate->is_enabled,
        ]);

        if ($request->wantsJson()) {
             return response()->json(['success' => true, 'is_enabled' => $emailTemplate->is_enabled]);
        }

        $status = $emailTemplate->is_enabled ? 'enabled' : 'disabled';
        return back()->with('success', "Email template {$status} successfully.")->with('email_subtab', 'templates');
    }

    /**
     * Reset email template to default.
     */
    public function resetEmailTemplate(string $journal, string $emailTemplateId): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $emailTemplate = EmailTemplate::findOrFail($emailTemplateId);

        if ($emailTemplate->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        if ($emailTemplate->resetToDefault()) {
            return back()->with('success', 'Email template reset to default.')->with('email_subtab', 'templates');
        }

        return back()->with('error', 'Could not find default template.')->with('email_subtab', 'templates');
    }

    // =====================================================
    // NOTIFICATION TEMPLATES
    // =====================================================

    /**
     * Update a notification template.
     */
    public function updateNotificationTemplate(Request $request, string $journal, string $eventKey): RedirectResponse
    {
        $currentJournal = current_journal();
        if (!$currentJournal) {
             abort(404);
        }

        $validated = $request->validate([
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);

        \App\Models\NotificationTemplate::updateOrCreate(
            [
                'journal_id' => $currentJournal->id,
                'event_key' => $eventKey,
                'channel' => 'whatsapp'
            ],
            [
                'body' => $validated['body'],
                'is_active' => $request->boolean('is_active'),
            ]
        );

        return back()->with('success', 'Notification template updated successfully.');
    }

    /**
     * Toggle WhatsApp notifications for the journal.
     */
    public function toggleWhatsappNotifications(Request $request, string $journal)
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'Journal not found'], 404);
            }
            abort(404, 'Journal not found.');
        }

        $validated = $request->validate([
            'enabled' => 'required|boolean',
        ]);

        $currentJournal->update([
            'wa_notifications_enabled' => $validated['enabled'],
        ]);

        if ($request->wantsJson()) {
             return response()->json([
                 'success' => true, 
                 'message' => 'WhatsApp notifications ' . ($currentJournal->wa_notifications_enabled ? 'enabled' : 'disabled') . ' successfully.',
                 'enabled' => $currentJournal->wa_notifications_enabled
             ]);
        }

        $status = $currentJournal->wa_notifications_enabled ? 'enabled' : 'disabled';
        return back()->with('success', "WhatsApp notifications {$status} successfully.");
    }
}

