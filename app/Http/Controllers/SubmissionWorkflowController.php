<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\DiscussionFile;
use App\Models\DiscussionMessage;
use App\Models\EditorialAssignment;
use App\Models\Journal;
use App\Http\Controllers\Controller;
use App\Models\CopyeditingAssignment;
use App\Models\ProductionAssignment;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\SubmissionLog;
use App\Models\ReviewAssignment;
use App\Models\ReviewRound;
use App\Services\FileUploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SubmissionWorkflowController extends Controller
{
    public function __construct(
        protected FileUploadSecurityService $uploadSecurity
    ) {}
    /**
     * Get the current journal from context.
     */
    protected function getJournal(): Journal
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal context not found.');
        }

        return $journal;
    }

    /**
     * Display the workflow page for a submission (OJS 3.3 style).
     */
    public function show(string $journalSlug, Submission $submission): View
    {
        $journal = $this->getJournal();

        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        $user = auth()->user();

        // Always false in this controller as it is workflow (editor/manager) view
        $isAuthorView = false;

        $submission->load([
            'journal',
            'section',
            'issue',
            'authors',
            'files',
            'discussions.user',
            'discussions.messages.user',
            'discussions.messages.files',
            'discussions.participants',
            'editorialAssignments.user',
            'reviewAssignments.reviewer',
            'reviewAssignments.reviewAttachments',
        ]);

        $issues = \App\Models\Issue::where('journal_id', $journal->id)
            ->orderBy('year', 'desc')
            ->orderBy('volume', 'desc')
            ->orderBy('number', 'desc')
            ->get();

        $issueOptions = $issues->map(function ($issue) {
            return [
                'id' => $issue->id,
                'label' => $issue->identifier . ($issue->published_at ? ' (Published)' : ' (Unpublished)'),
            ];
        })->values();

        // Prepare participants for discussion modal (Author + Editors)
        $participants = collect();
        if ($submission->author) {
            $participants->push($submission->author);
        }
        foreach ($submission->editorialAssignments->where('is_active', true) as $assignment) {
            if ($assignment->user && !$participants->contains('id', $assignment->user->id)) {
                $participants->push($assignment->user);
            }
        }
        if (!$participants->contains('id', $user->id)) {
            $participants->push($user);
        }

        // Potential Editors for Assignment
        $activeEditorIds = $submission->editorialAssignments
            ->where('is_active', true)
            ->pluck('user_id')
            ->filter()
            ->toArray();

        $potentialEditors = \App\Models\User::whereHas('journalRoles', function ($query) use ($journal) {
            $query->where('journal_id', $journal->id)
                  ->whereHas('role', function ($q) {
                      $q->where('permit_submission', 1);
                  });
        })
        ->whereDoesntHave('submissionAuthors', function ($q) use ($submission) {
            $q->where('submission_id', $submission->id);
        })
        ->whereNotIn('id', $activeEditorIds)
        ->with(['journalRoles' => function($q) use ($journal) {
            $q->where('journal_id', $journal->id)->with('role');
        }])
        ->get()
        ->map(function ($user) use ($journal) {
            $roles = $user->journalRoles->where('journal_id', $journal->id)->map(fn($jr) => $jr->role->name)->toArray();
            $arr = $user->toArray();
            $arr['role_names'] = $roles;
            $arr['role_display'] = implode(', ', $roles);
            return $arr;
        })
        ->values();

        // Potential Participants for stage-specific assignment
        $potentialParticipants = [];

        // Review stage participants
        $currentReviewRound = $submission->currentReviewRound();
        $assignedReviewerIds = $currentReviewRound 
            ? $currentReviewRound->reviewAssignments()->pluck('reviewer_id')->filter()->toArray()
            : [];

        $potentialParticipants['review'] = \App\Models\User::whereHas('journalRoles', function ($query) use ($journal) {
            $query->where('journal_id', $journal->id)
                  ->whereHas('role', function ($q) {
                      $q->where('name', 'Reviewer');
                  });
        })
        ->whereNotIn('id', $assignedReviewerIds)
        ->with(['journalRoles' => function($q) use ($journal) {
            $q->where('journal_id', $journal->id)->with('role');
        }])
        ->get()
        ->map(function ($user) use ($journal) {
            $roles = $user->journalRoles->where('journal_id', $journal->id)->map(fn($jr) => $jr->role->name)->toArray();
            $arr = $user->toArray();
            $arr['role_names'] = $roles;
            $arr['role_display'] = implode(', ', $roles);
            return $arr;
        })
        ->values();

        // Copyediting stage participants
        $potentialParticipants['copyediting'] = \App\Models\User::whereHas('journalRoles', function ($query) use ($journal) {
            $query->where('journal_id', $journal->id)
                  ->whereHas('role', function ($q) {
                      $q->whereIn('name', ['Copyeditor', 'Layout Editor']);
                  });
        })
        ->with(['journalRoles' => function($q) use ($journal) {
            $q->where('journal_id', $journal->id)->with('role');
        }])
        ->get()
        ->map(function ($user) use ($journal) {
            $roles = $user->journalRoles->where('journal_id', $journal->id)->map(fn($jr) => $jr->role->name)->toArray();
            $arr = $user->toArray();
            $arr['role_names'] = $roles;
            $arr['role_display'] = implode(', ', $roles);
            return $arr;
        })
        ->values();

        // Production stage participants
        $potentialParticipants['production'] = \App\Models\User::whereHas('journalRoles', function ($query) use ($journal) {
            $query->where('journal_id', $journal->id)
                  ->whereHas('role', function ($q) {
                      $q->whereIn('name', ['Layout Editor', 'Proofreader']);
                  });
        })
        ->with(['journalRoles' => function($q) use ($journal) {
            $q->where('journal_id', $journal->id)->with('role');
        }])
        ->get()
        ->map(function ($user) use ($journal) {
            $roles = $user->journalRoles->where('journal_id', $journal->id)->map(fn($jr) => $jr->role->name)->toArray();
            $arr = $user->toArray();
            $arr['role_names'] = $roles;
            $arr['role_display'] = implode(', ', $roles);
            return $arr;
        })
        ->values();

        // SEO Analysis
        $validator = new \App\Services\GoogleScholarValidator();
        $seoAnalysis = $validator->validate($submission);

        $renderedEmailTemplates = \App\Services\JournalEmailService::getSubmissionEmailTemplates($journal, $submission);

        return view('submissions.show', compact(
            'submission',
            'journal',
            'issues',
            'issueOptions',
            'participants',
            'isAuthorView',
            'potentialEditors',
            'potentialParticipants',
            'seoAnalysis',
            'renderedEmailTemplates'
        ));
    }


    /**
     * Assign an editor to the submission.
     */
    public function assignEditor(Request $request, string $journalSlug, Submission $submission): RedirectResponse
    {
        $journal = $this->getJournal();

        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        // Determine role based on user's roles in this journal
        $user = \App\Models\User::find($validated['user_id']);
        $journalRole = $user->journalRoles()->where('journal_id', $journal->id)->first();
        
        // Default to 'editor' if no specific role found or if they are a manager/admin
        $role = 'editor'; 
        if ($journalRole && $journalRole->role->name === 'Section Editor') {
            $role = 'section_editor';
        }

        // Check if assignment record already exists (active or inactive)
        try {
            $assignment = EditorialAssignment::where('submission_id', $submission->id)
                ->where('user_id', $validated['user_id'])
                ->first();

            if ($assignment) {
                if ($assignment->is_active) {
                    return back()->with('error', 'This user is already assigned to this submission.');
                }
                
                // Reactivate and update the existing assignment record
                $assignment->update([
                    'is_active' => true,
                    'assigned_by' => auth()->id(),
                    'role' => $role,
                    'date_assigned' => now(),
                ]);
            } else {
                // Create a new assignment record
                EditorialAssignment::create([
                    'submission_id' => $submission->id,
                    'user_id' => $validated['user_id'],
                    'assigned_by' => auth()->id(),
                    'role' => $role,
                    'date_assigned' => now(),
                ]);
            }
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Already created by a parallel request, ignore and continue
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() != 23505) {
                throw $e;
            }
            // Already created by a parallel request, ignore and continue
        }

        // Notify the assigned editor (Standard OJS Editor Assignment Notification)
        if ($user) {
            try {
                $user->notify(new \App\Notifications\EditorAssignmentNotification($submission, auth()->user()));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send editor assignment email: ' . $e->getMessage());
            }

            // Log the event
            SubmissionLog::log(
                submission:  $submission,
                eventType:   SubmissionLog::EVENT_EDITOR_ASSIGNED,
                title:       'Editor Assigned',
                description: auth()->user()->name . " assigned {$user->name} as " . ucfirst(str_replace('_', ' ', $role)) . ".",
                metadata:    ['editor_id' => $user->id, 'role' => $role],
                stage:       $submission->stage,
            );
        }

        return back()->with('success', 'Editor assigned successfully.');
    }

    /**
     * Remove an editor assignment.
     */
    public function removeEditor(string $journalSlug, Submission $submission, EditorialAssignment $assignment): RedirectResponse
    {
        $journal = $this->getJournal();

        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        $removedUser = $assignment->user;
        $assignment->update(['is_active' => false]);

        if ($removedUser) {
            try {
                // Notify the removed editor
                $removedUser->notify(new \App\Notifications\WorkflowEventNotification(
                    $submission,
                    'Editor Unassigned',
                    "You have been unassigned from the submission: \"{$submission->title}\" by " . auth()->user()->name . ".",
                    url("/{$journal->slug}/submissions/{$submission->url_slug}")
                ));

                // Notify other assigned editors
                $otherEditors = $submission->activeEditors()
                    ->where('user_id', '!=', $removedUser->id)
                    ->where('user_id', '!=', auth()->id())
                    ->with('user')->get()
                    ->map(fn($a) => $a->user)
                    ->filter();

                foreach ($otherEditors as $otherEditor) {
                    $otherEditor->notify(new \App\Notifications\WorkflowEventNotification(
                        $submission,
                        'Editor Unassigned',
                        "{$removedUser->name} has been unassigned from the submission: \"{$submission->title}\" by " . auth()->user()->name . ".",
                        url("/{$journal->slug}/submissions/{$submission->url_slug}")
                    ));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to send editor unassignment notification: ' . $e->getMessage());
            }
        }

        // Log the unassign event
        SubmissionLog::log(
            submission:  $submission,
            eventType:   SubmissionLog::EVENT_EDITOR_UNASSIGNED,
            title:       'Editor Unassigned',
            description: auth()->user()->name . " unassigned " . ($removedUser ? $removedUser->name : 'Editor') . ".",
            metadata:    ['editor_id' => $removedUser?->id],
            stage:       $submission->stage,
        );

        return back()->with('success', 'Editor assignment removed.');
    }

    /**
     * Assign a production staff member to a submission.
     * 
     * Requirements: 5.3, 5.4, 5.5, 8.4
     */
    public function assignProduction(Request $request, string $journalSlug, Submission $submission): RedirectResponse
    {
        $journal = $this->getJournal();

        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        // Validate request data (modal sends 'user_id')
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'role' => 'nullable|string|max:255',
        ]);

        $productionUserId = $validated['user_id'];

        // Check for duplicate assignment (exclude cancelled)
        $existing = ProductionAssignment::where('submission_id', $submission->id)
            ->where('production_user_id', $productionUserId)
            ->where('status', '!=', ProductionAssignment::STATUS_CANCELLED)
            ->exists();

        if ($existing) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => ['This production staff is already assigned to this submission.']
            ]);
        }

        try {
            DB::transaction(function () use ($productionUserId, $validated, $submission, $journal) {
                // Create ProductionAssignment record
                $assignment = ProductionAssignment::create([
                    'submission_id' => $submission->id,
                    'production_user_id' => $productionUserId,
                    'assigned_by' => auth()->id(),
                    'role' => $validated['role'] ?? null,
                    'status' => ProductionAssignment::STATUS_ASSIGNED,
                    'date_assigned' => now(),
                ]);

                // Send notification to assigned production staff
                $productionUser = \App\Models\User::find($productionUserId);
                
                if ($productionUser) {
                    try {
                        $productionUser->notify(new \App\Notifications\WorkflowEventNotification(
                            $submission,
                            'Production Assignment',
                            "You have been assigned to the production stage of the submission: \"{$submission->title}\" by " . auth()->user()->name . ".",
                            url("/{$journal->slug}/submissions/{$submission->url_slug}")
                        ));
                        
                        $assignment->update(['date_notified' => now()]);
                    } catch (\Throwable $e) {
                        Log::error('Production assignment notification failed', [
                            'submission_id' => $submission->id,
                            'production_user_id' => $productionUser->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }

                    // Log the assignment action
                    SubmissionLog::log(
                        submission: $submission,
                        eventType: SubmissionLog::EVENT_PARTICIPANT_ADDED,
                        title: 'Production Staff Assigned',
                        description: auth()->user()->name . " assigned {$productionUser->name} to the production stage" . 
                                    ($validated['role'] ? " as {$validated['role']}" : "") . ".",
                        metadata: [
                            'production_user_id' => $productionUser->id,
                            'role' => $validated['role'] ?? null,
                            'assignment_id' => $assignment->id,
                        ],
                        stage: $submission->stage,
                    );
                }
            });

            // Return success response with redirect
            return redirect()
                ->route('journal.submissions.show', [
                    'journal' => $journal->slug,
                    'submission' => $submission->slug
                ])
                ->with('success', 'Production staff assigned successfully.');

        } catch (\Throwable $e) {
            Log::error('Assign production staff failed', [
                'submission_id' => $submission->id,
                'journal_id' => $submission->journal_id,
                'production_user_id' => $productionUserId ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to assign production staff. Please check logs.');
        }
    }

    /**
     * Assign a copyeditor to a submission.
     * 
     * Requirements: 5.2, 5.4, 5.5, 8.4
     */
    public function assignCopyeditor(Request $request, string $journalSlug, Submission $submission): RedirectResponse
    {
        $journal = $this->getJournal();

        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        // Validate request data (modal sends 'user_id')
        $validated = $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
        ]);

        $copyeditorId = $validated['user_id'];

        // Check for duplicate assignment (exclude cancelled)
        $existing = CopyeditingAssignment::where('submission_id', $submission->id)
            ->where('copyeditor_id', $copyeditorId)
            ->where('status', '!=', CopyeditingAssignment::STATUS_CANCELLED)
            ->exists();

        if ($existing) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'user_id' => ['This copyeditor is already assigned to this submission.']
            ]);
        }

        try {
            DB::transaction(function () use ($copyeditorId, $submission, $journal) {
                // Create CopyeditingAssignment record
                $assignment = CopyeditingAssignment::create([
                    'submission_id' => $submission->id,
                    'copyeditor_id' => $copyeditorId,
                    'assigned_by' => auth()->id(),
                    'status' => CopyeditingAssignment::STATUS_PENDING,
                    'assigned_at' => now(),
                ]);

                // Send notification to assigned copyeditor
                $copyeditor = \App\Models\User::find($copyeditorId);
                
                if ($copyeditor) {
                    try {
                        $copyeditor->notify(new \App\Notifications\WorkflowEventNotification(
                            $submission,
                            'Copyediting Assignment',
                            "You have been assigned to copyedit the submission: \"{$submission->title}\" by " . auth()->user()->name . ".",
                            url("/{$journal->slug}/submissions/{$submission->url_slug}")
                        ));
                    } catch (\Throwable $e) {
                        Log::error('Copyeditor assignment notification failed', [
                            'submission_id' => $submission->id,
                            'copyeditor_id' => $copyeditor->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                        ]);
                    }

                    // Log the assignment action
                    SubmissionLog::log(
                        submission: $submission,
                        eventType: SubmissionLog::EVENT_PARTICIPANT_ADDED,
                        title: 'Copyeditor Assigned',
                        description: auth()->user()->name . " assigned {$copyeditor->name} as copyeditor.",
                        metadata: [
                            'copyeditor_id' => $copyeditor->id,
                            'assignment_id' => $assignment->id,
                        ],
                        stage: $submission->stage,
                    );
                }
            });

            // Return success response with redirect
            return redirect()
                ->route('journal.submissions.show', [
                    'journal' => $journal->slug,
                    'submission' => $submission->slug
                ])
                ->with('success', 'Copyeditor assigned successfully.');

        } catch (\Throwable $e) {
            Log::error('Assign copyeditor failed', [
                'submission_id' => $submission->id,
                'journal_id' => $submission->journal_id,
                'copyeditor_id' => $copyeditorId ?? null,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to assign copyeditor. Please check logs.');
        }
    }

    /**
     * Change submission stage (OJS 3.3 workflow).
     */
    public function changeStage(Request $request, string $journalSlug, Submission $submission): RedirectResponse
    {
        $journal = $this->getJournal();

        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        $validated = $request->validate([
            'stage_id' => 'required|integer|in:1,2,3,4',
            'action' => 'nullable|string',
        ]);

        $newStageId = (int) $validated['stage_id'];
        $action = $validated['action'] ?? null;

        DB::beginTransaction();

        try {
            $oldStageId = $submission->stage_id;

            // Update stage
            $submission->update([
                'stage_id' => $newStageId,
                'stage' => $this->getStageNameById($newStageId),
            ]);

            // Handle specific actions
            switch ($action) {
                case 'send_to_review':
                    $submission->update(['status' => Submission::STATUS_IN_REVIEW]);
                    break;

                case 'accept':
                    $submission->update([
                        'status' => Submission::STATUS_ACCEPTED,
                        'accepted_at' => now(),
                    ]);
                    break;

                case 'request_revisions':
                    $submission->update(['status' => Submission::STATUS_REVISION_REQUIRED]);
                    break;

                case 'decline':
                    $submission->update(['status' => Submission::STATUS_REJECTED]);
                    break;
            }

            // Notify author and other assigned editors if action is performed
            if ($action) {
                // Notify author
                if ($submission->author) {
                    try {
                        $notificationDecision = match ($action) {
                            'send_to_review' => 'under_review',
                            'accept' => 'accepted',
                            'request_revisions' => 'revision_required',
                            'decline' => 'rejected',
                            default => null,
                        };
                        if ($notificationDecision) {
                            $submission->author->notify(new \App\Notifications\SubmissionDecision($submission, $notificationDecision));
                        }
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed to notify author on stage action: ' . $e->getMessage());
                    }
                }

                // Stage transition notifications now handled centrally by SubmissionLog::log
            }

            DB::commit();

            // Audit log the stage change
            $stageNames = [1 => 'Submission', 2 => 'Review', 3 => 'Copyediting', 4 => 'Production'];
            SubmissionLog::log(
                submission:  $submission,
                eventType:   SubmissionLog::EVENT_STAGE_CHANGED,
                title:       'Stage Changed to ' . ($stageNames[$newStageId] ?? $newStageId),
                description: auth()->user()->name . ' changed the workflow stage' . ($action ? " (action: $action)" : '') . '.',
                metadata:    ['from_stage' => $oldStageId, 'to_stage' => $newStageId, 'action' => $action],
                stage:       SubmissionLog::stageFromId($newStageId),
            );

            return back()->with('success', $this->getStageChangeMessage($oldStageId, $newStageId, $action));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to change stage: ' . $e->getMessage());
        }
    }

    /**
     * Schedule submission for publication.
     */
    public function schedulePublication(Request $request, string $journalSlug, Submission $submission): RedirectResponse
    {
        $journal = $this->getJournal();

        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        $validated = $request->validate([
            'issue_id' => 'required|uuid|exists:issues,id',
        ]);

        $submission->update([
            'issue_id' => $validated['issue_id'],
            'status' => Submission::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        return back()->with('success', 'Submission scheduled for publication.');
    }

    /**
     * Get stage name by ID.
     */
    private function getStageNameById(int $stageId): string
    {
        return match ($stageId) {
            1 => Submission::STAGE_SUBMISSION,
            2 => Submission::STAGE_REVIEW,
            3 => Submission::STAGE_COPYEDITING,
            4 => Submission::STAGE_PRODUCTION,
            default => Submission::STAGE_SUBMISSION,
        };
    }

    /**
     * Get message for stage change.
     */
    private function getStageChangeMessage(int $oldStage, int $newStage, ?string $action): string
    {
        if ($action === 'accept') {
            return 'Submission accepted and moved to copyediting.';
        }

        if ($action === 'request_revisions') {
            return 'Revisions requested from author.';
        }

        if ($action === 'decline') {
            return 'Submission declined.';
        }

        $stageNames = [
            1 => 'Submission',
            2 => 'Review',
            3 => 'Copyediting',
            4 => 'Production',
        ];

        return "Submission moved to {$stageNames[$newStage]} stage.";
    }
    /**
     * Upload a file to the submission.
     */
    public function uploadFile(Request $request, string $journalSlug, Submission $submission): RedirectResponse
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        $request->validate([
            'file' => 'required|file',
            'stage' => 'required|string|in:submission,review,copyedit_draft,copyedited,production',
        ]);

        // Security: validate file via FileUploadSecurityService
        $this->uploadSecurity->validate($request->file('file'), 'manuscript', $request);

        // Map copyedit_draft and copyedited back to copyediting for policy check
        $policyStage = in_array($request->stage, ['copyedit_draft', 'copyedited']) ? 'copyediting' : $request->stage;
        $this->authorize('accessStage', [$submission, $policyStage]);

        $file = $request->file('file');
        $path = $file->store("journals/{$journal->id}/submissions/{$submission->id}/files");

        $submissionFile = SubmissionFile::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'submission_id' => $submission->id,
            'uploaded_by' => auth()->id(),
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => 'document',
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'stage' => $request->stage,
            'version' => 1,
        ]);

        // Audit log with file reference
        SubmissionLog::log(
            submission:  $submission,
            eventType:   SubmissionLog::EVENT_FILE_UPLOADED,
            title:       'File Uploaded: ' . $file->getClientOriginalName(),
            description: auth()->user()->name . ' uploaded a file to the ' . $request->stage . ' stage.',
            metadata:    ['file_name' => $file->getClientOriginalName(), 'file_size' => $file->getSize()],
            fileIds:     [$submissionFile->id],
            stage:       $request->stage,
        );

        return back()->with('success', 'File uploaded successfully.');
    }

    /**
     * Store a new discussion.
     */
    public function storeDiscussion(Request $request, string $journalSlug, Submission $submission): RedirectResponse
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'stage_id' => 'required|integer',
        ]);

        // Map stage_id to string for policy check
        $stageNames = [1 => 'submission', 2 => 'review', 3 => 'copyediting', 4 => 'production'];
        $stageName = $stageNames[$request->stage_id] ?? 'submission';
        $this->authorize('accessStage', [$submission, $stageName]);

        $discussion = Discussion::create([
            'submission_id' => $submission->id,
            'user_id' => auth()->id(),
            'subject' => $request->subject,
            'stage_id' => $request->stage_id,
            'is_open' => true,
        ]);

        DiscussionMessage::create([
            'discussion_id' => $discussion->id,
            'user_id' => auth()->id(),
            'body' => $request->message,
        ]);

        return back()->with('success', 'Discussion started.');
    }

    /**
     * Get all available files for promotion (Submission Files + Discussion Files).
     * Used by the Send to Review modal.
     */
    public function getAvailableFiles(string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        // 1. Get Submission Files from current stage
        $submissionFiles = $submission->files()
            ->where('stage', 'submission')
            ->get()
            ->map(function ($file) {
                return [
                    'id' => $file->id,
                    'name' => $file->file_name,
                    'size' => $file->file_size,
                    'type' => 'submission_file',
                    'source' => 'Submission Files',
                    'created_at' => $file->created_at->format('M d, Y'),
                ];
            });

        // 2. Get Discussion Files from pre-review discussions (stage_id = 1)
        $discussionFiles = DiscussionFile::whereHas('message.discussion', function ($q) use ($submission) {
            $q->where('submission_id', $submission->id)
                ->where('stage_id', 1);
        })->get()->map(function ($file) {
            return [
                'id' => $file->id,
                'name' => $file->original_name,
                'size' => $file->file_size,
                'type' => 'discussion_file',
                'source' => 'Pre-Review Discussions',
                'created_at' => $file->created_at->format('M d, Y'),
            ];
        });

        return response()->json([
            'files' => $submissionFiles->merge($discussionFiles)->values(),
        ]);
    }

    /**
     * Promote submission to Review stage with file selection.
     * Copies selected files to the review stage.
     */
    public function promoteToReview(Request $request, string $journalSlug, Submission $submission): RedirectResponse
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }
        $validated = $request->validate([
            'selected_files' => 'nullable|array',
            'selected_files.*.id' => 'required|uuid',
            'selected_files.*.type' => 'required|in:submission_file,discussion_file',
        ]);

        DB::beginTransaction();

        try {
            // Update submission stage to Review (2)
            $submission->update([
                'stage_id' => 2,
                'stage' => Submission::STAGE_REVIEW,
                'status' => Submission::STATUS_IN_REVIEW,
            ]);

            // Create initial review round if not exists
            $reviewRound = $submission->currentReviewRound();
            if (!$reviewRound) {
                ReviewRound::create([
                    'submission_id' => $submission->id,
                    'round' => 1,
                    'status' => ReviewRound::STATUS_PENDING,
                ]);
            }

            // Copy selected files to review stage
            $submissionFileIds = [];
            if (!empty($validated['selected_files'])) {
                foreach ($validated['selected_files'] as $fileData) {
                    if ($fileData['type'] === 'submission_file') {
                        // Copy submission file
                        $originalFile = SubmissionFile::find($fileData['id']);
                        if ($originalFile) {
                            $existingFile = SubmissionFile::where('submission_id', $submission->id)
                                ->where('stage', 'review')
                                ->where('file_path', $originalFile->file_path)
                                ->first();

                            if ($existingFile) {
                                $submissionFileIds[] = $existingFile->id;
                            } else {
                                $submissionFile = SubmissionFile::create([
                                    'id' => (string) Str::uuid(),
                                    'submission_id' => $submission->id,
                                    'uploaded_by' => auth()->id(),
                                    'file_path' => $originalFile->file_path,
                                    'file_name' => $originalFile->file_name,
                                    'file_type' => $originalFile->file_type,
                                    'mime_type' => $originalFile->mime_type,
                                    'file_size' => $originalFile->file_size,
                                    'stage' => 'review',
                                    'version' => 1,
                                    'metadata' => [
                                        'copied_from' => $originalFile->id,
                                        'copied_at' => now()->toISOString(),
                                    ],
                                ]);
                                $submissionFileIds[] = $submissionFile->id;
                            }
                        }
                    } elseif ($fileData['type'] === 'discussion_file') {
                        // Copy discussion file to submission files
                        $discussionFile = DiscussionFile::find($fileData['id']);
                        if ($discussionFile) {
                            $existingFile = SubmissionFile::where('submission_id', $submission->id)
                                ->where('stage', 'review')
                                ->where('file_path', $discussionFile->file_path)
                                ->first();

                            if ($existingFile) {
                                $submissionFileIds[] = $existingFile->id;
                            } else {
                                $submissionFile = SubmissionFile::create([
                                    'id' => (string) Str::uuid(),
                                    'submission_id' => $submission->id,
                                    'uploaded_by' => auth()->id(),
                                    'file_path' => $discussionFile->file_path,
                                    'file_name' => $discussionFile->original_name,
                                    'file_type' => $discussionFile->file_type ?? 'document',
                                    'mime_type' => 'application/octet-stream',
                                    'file_size' => $discussionFile->file_size,
                                    'stage' => 'review',
                                    'version' => 1,
                                    'metadata' => [
                                        'copied_from_discussion' => $discussionFile->id,
                                        'copied_at' => now()->toISOString(),
                                    ],
                                ]);
                                $submissionFileIds[] = $submissionFile->id;
                            }
                        }
                    }
                }
            }

            DB::commit();

            // Notify corresponding author that submission has entered Review stage
            try {
                $author = $submission->author; // The submitting user
                if ($author) {
                    $submissionUrl = route('journal.submissions.show', [
                        'journal' => $journal->slug,
                        'submission' => $submission->url_slug ?? $submission->slug
                    ]);
                    
                    $vars = [
                        'submissionTitle' => $submission->title,
                        'submissionUrl' => $submissionUrl,
                        'authorName' => $author->name ?? $author->full_name ?? 'Author',
                    ];

                    $emailSent = \App\Services\JournalEmailService::sendNotification(
                        $journal,
                        $author,
                        'SUBMISSION_UNDER_REVIEW',
                        $vars
                    );

                    // Fallback to default notification if email template fails/disabled
                    if (!$emailSent && method_exists($author, 'notify')) {
                        $author->notify(new \App\Notifications\SubmissionDecision($submission, 'under_review'));
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Failed to notify author on send to review: ' . $e->getMessage());
            }

            // Audit log the stage transition
            SubmissionLog::log(
                submission:  $submission,
                eventType:   SubmissionLog::EVENT_STAGE_CHANGED,
                title:       'Sent to Review Stage',
                description: auth()->user()->name . ' promoted the submission to the Review stage with ' . count($validated['selected_files'] ?? []) . ' file(s).',
                metadata:    ['file_count' => count($validated['selected_files'] ?? [])],
                fileIds:     $submissionFileIds,
                stage:       Submission::STAGE_REVIEW,
            );

            return redirect(route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $submission]) . '?tab=workflow&stage=review')
                ->with('success', 'Submission sent to Review stage. ' . count($validated['selected_files'] ?? []) . ' file(s) promoted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to promote to review: ' . $e->getMessage());
        }
    }

    /**
     * Accept submission and skip review stage directly to Copyediting.
     * Used for trusted authors or fast-track submissions.
     */
    public function skipReview(Request $request, string $journalSlug, Submission $submission): RedirectResponse
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        $validated = $request->validate([
            'selected_files' => 'nullable|array',
            'selected_files.*.id' => 'required|uuid',
            'selected_files.*.type' => 'required|in:submission_file,discussion_file',
            'notes' => 'nullable|string|max:2000',
        ]);

        DB::beginTransaction();

        try {
            $metadata = $submission->metadata ?? [];
            $metadata['decisions'] = $metadata['decisions'] ?? [];
            $metadata['decisions'][] = [
                'decision' => 'accept',
                'made_by' => auth()->id(),
                'made_at' => now()->toISOString(),
                'notes' => $validated['notes'] ?? null,
            ];

            // Update submission stage directly to Copyediting (3)
            $submission->update([
                'stage_id' => 3,
                'stage' => Submission::STAGE_COPYEDITING,
                'status' => Submission::STATUS_ACCEPTED,
                'accepted_at' => now(),
                'metadata' => $metadata,
            ]);

            // Copy selected files to copyediting stage
            $submissionFileIds = [];
            if (!empty($validated['selected_files'])) {
                foreach ($validated['selected_files'] as $fileData) {
                    if ($fileData['type'] === 'submission_file') {
                        $originalFile = SubmissionFile::find($fileData['id']);
                        if ($originalFile) {
                            $submissionFile = SubmissionFile::create([
                                'id' => (string) Str::uuid(),
                                'submission_id' => $submission->id,
                                'uploaded_by' => auth()->id(),
                                'file_path' => $originalFile->file_path,
                                'file_name' => $originalFile->file_name,
                                'file_type' => $originalFile->file_type,
                                'mime_type' => $originalFile->mime_type,
                                'file_size' => $originalFile->file_size,
                                'stage' => 'copyediting',
                                'version' => 1,
                                'metadata' => [
                                    'copied_from' => $originalFile->id,
                                    'skip_review' => true,
                                    'copied_at' => now()->toISOString(),
                                ],
                            ]);
                            $submissionFileIds[] = $submissionFile->id;
                        }
                    } elseif ($fileData['type'] === 'discussion_file') {
                        $discussionFile = DiscussionFile::find($fileData['id']);
                        if ($discussionFile) {
                            $submissionFile = SubmissionFile::create([
                                'id' => (string) Str::uuid(),
                                'submission_id' => $submission->id,
                                'uploaded_by' => auth()->id(),
                                'file_path' => $discussionFile->file_path,
                                'file_name' => $discussionFile->original_name,
                                'file_type' => $discussionFile->file_type ?? 'document',
                                'mime_type' => 'application/octet-stream',
                                'file_size' => $discussionFile->file_size,
                                'stage' => 'copyediting',
                                'version' => 1,
                                'metadata' => [
                                    'copied_from_discussion' => $discussionFile->id,
                                    'skip_review' => true,
                                    'copied_at' => now()->toISOString(),
                                ],
                            ]);
                            $submissionFileIds[] = $submissionFile->id;
                        }
                    }
                }
            }

            // Log the skip review action
            if (!empty($validated['notes'])) {
                Discussion::create([
                    'submission_id' => $submission->id,
                    'user_id' => auth()->id(),
                    'subject' => 'Review Skipped - Direct Accept',
                    'stage_id' => 3,
                    'is_open' => false,
                ]);
            }

            DB::commit();

            // Audit log the skip-review stage transition
            SubmissionLog::log(
                submission:  $submission,
                eventType:   SubmissionLog::EVENT_DECISION_MADE,
                title:       'Skip Review – Sent to Copyediting',
                description: auth()->user()->name . ' accepted the submission and skipped review, moving it directly to Copyediting.',
                metadata:    ['decision' => 'accepted'],
                fileIds:     $submissionFileIds,
                stage:       Submission::STAGE_COPYEDITING,
            );

            return redirect(route('journal.submissions.show', ['journal' => $journal->slug, 'submission' => $submission]) . '?tab=workflow&stage=copyediting')
                ->with('success', 'Submission accepted and moved directly to Copyediting.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to skip review: ' . $e->getMessage());
        }
    }

    /**
     * Decline a submission with reason/email log.
     */
    public function decline(Request $request, string $journalSlug, Submission $submission): RedirectResponse
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        $validated = $request->validate([
            'reason' => 'required|string|min:10|max:5000',
            'notify_author' => 'nullable',
        ]);

        DB::beginTransaction();

        try {
            // Update submission status to rejected
            $submission->update([
                'status' => Submission::STATUS_REJECTED,
            ]);
            // Log the decline reason as metadata
            $metadata = $submission->metadata ?? [];
            $metadata['decline_log'] = [
                'declined_by' => auth()->id(),
                'declined_at' => now()->toISOString(),
                'reason' => $validated['reason'],
            ];
            $submission->update(['metadata' => $metadata]);

            // Create a discussion entry for the decline reason (visible in archives)
            $discussion = Discussion::create([
                'submission_id' => $submission->id,
                'user_id' => auth()->id(),
                'subject' => 'Submission Declined',
                'stage_id' => $submission->stage_id,
                'is_open' => false,
            ]);

            DiscussionMessage::create([
                'discussion_id' => $discussion->id,
                'user_id' => auth()->id(),
                'body' => '<p><strong>Reason for Declining:</strong></p>' . nl2br(e($validated['reason'])),
            ]);

            // Log the decline decision and trigger centralized notifications
            SubmissionLog::log(
                submission:   $submission,
                eventType:    SubmissionLog::EVENT_DECISION_MADE,
                title:        'Submission Declined',
                description:  auth()->user()->name . ' declined the submission.',
                metadata:     [
                    'decision' => 'rejected',
                    'comments' => $validated['reason'],
                    'notify_author' => !empty($validated['notify_author'])
                ],
                user:         auth()->user(),
                fileIds:      [],
                stage:        $submission->stage,
            );

            DB::commit();

            return back()->with('success', 'Submission has been declined.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to decline submission: ' . $e->getMessage(), [
                'submission_id' => $submission->id,
                'user_id'       => auth()->id(),
            ]);
            return back()->with('error', 'Failed to decline submission. Please try again.');
        }
    }
}
