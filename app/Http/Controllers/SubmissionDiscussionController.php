<?php

namespace App\Http\Controllers;

use App\Models\Discussion;
use App\Models\DiscussionMessage;
use App\Models\DiscussionFile;
use App\Models\Submission;
use App\Models\Journal;
use App\Models\User;
use App\Notifications\NewDiscussionMessageNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SubmissionDiscussionController extends Controller
{
    private function getJournal()
    {
        return current_journal();
    }

    /**
     * Store a new discussion.
     */
    public function store(Request $request, string $journalSlug, Submission $submission)
    {
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);

        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'stage_id' => 'required|integer',
            'attached_files' => 'nullable|array',
            'attached_files.*.id' => 'required|exists:discussion_files,id',
            'attached_files.*.name' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $submission) {
            // 1. Create Discussion
            $discussion = Discussion::create([
                'submission_id' => $submission->id,
                'user_id' => auth()->id(),
                'subject' => $request->subject,
                'stage_id' => $request->stage_id,
                'is_open' => true,
            ]);

            // 2. Create Message
            $message = DiscussionMessage::create([
                'discussion_id' => $discussion->id,
                'user_id' => auth()->id(),
                'body' => $request->body,
            ]);

            // 3. Link Files
            if ($request->filled('attached_files')) {
                foreach ($request->attached_files as $fileData) {
                    DiscussionFile::where('id', $fileData['id'])
                        ->update([
                            'discussion_message_id' => $message->id,
                            'original_name' => $fileData['name'] ?? DB::raw('original_name'),
                        ]);
                }
            }

            // 4. Role Logic (Participants) - Placeholder as table doesn't exist
            // If Author, we effectively notify Editors.
        });

        return back()->with('success', 'Discussion created successfully.');
    }

    /**
     * Add a reply to a discussion.
     */
    public function storeReply(Request $request, string $journalSlug, Submission $submission, Discussion $discussion)
    {
        // Journal/Submission validation
        $journal = $this->getJournal();
        if ($submission->journal_id !== $journal->id) abort(404);
        if ($discussion->submission_id !== $submission->id) abort(404);

        $request->validate([
            'body' => 'required|string',
            'attached_files' => 'nullable|array',
            'attached_files.*.id' => 'required|exists:discussion_files,id',
        ]);

        $message = null;
        $savedDiscussion = $discussion;

        DB::transaction(function () use ($request, $discussion, &$message, &$savedDiscussion) {
            $message = DiscussionMessage::create([
                'discussion_id' => $discussion->id,
                'user_id' => auth()->id(),
                'body' => $request->body,
            ]);

            if ($request->filled('attached_files')) {
                foreach ($request->attached_files as $fileData) {
                    DiscussionFile::where('id', $fileData['id'])
                        ->update(['discussion_message_id' => $message->id]);
                }
            }
        });

        // ====== NOTIFICATION: Notify discussion participants ======
        if ($message) {
            // Get all users who have participated in this discussion (excluding current user)
            $participantIds = $savedDiscussion->messages()
                ->pluck('user_id')
                ->unique()
                ->reject(fn($id) => $id === auth()->id())
                ->toArray();

            // Also include the submission author if not already in participants
            $submission = $savedDiscussion->submission;
            if ($submission && $submission->user_id !== auth()->id() && !in_array($submission->user_id, $participantIds)) {
                $participantIds[] = $submission->user_id;
            }

            // Add the discussion creator if not already included
            if ($savedDiscussion->user_id !== auth()->id() && !in_array($savedDiscussion->user_id, $participantIds)) {
                $participantIds[] = $savedDiscussion->user_id;
            }

            $participants = User::whereIn('id', $participantIds)->get();
            $sender = auth()->user();

            foreach ($participants as $participant) {
                $participant->notify(new NewDiscussionMessageNotification($savedDiscussion, $message, $sender));
            }
        }

        return back()->with('success', 'Reply added.');
    }

    /**
     * Handle CKEditor Image Upload.
     */
    public function uploadCkeditorImage(Request $request)
    {
        if ($request->hasFile('upload')) {
            $file = $request->file('upload');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('uploads/ckeditor', $filename, 'public');
            $url = asset('storage/' . $path);

            return response()->json([
                'url' => $url,
                'uploaded' => true
            ]);
        }
        return response()->json(['uploaded' => false, 'error' => ['message' => 'No file uploaded']]);
    }

    /**
     * Upload Discussion File (Wizard Step 1).
     */
    public function uploadDiscussionFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB
        ]);

        $file = $request->file('file');
        // Store in a secure directory (not public)
        $path = $file->store('discussion-files');

        $discussionFile = DiscussionFile::create([
            'id' => (string) Str::uuid(),
            'user_id' => auth()->id(),
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'file_type' => 'document',
            'file_size' => $file->getSize(),
        ]);

        return response()->json([
            'id' => $discussionFile->id,
            'name' => $discussionFile->original_name,
            'size' => $discussionFile->file_size,
            'url' => route('journal.discussion.file.download', ['journal' => $this->getJournal()->slug, 'file' => $discussionFile->id]),
        ]);
    }

    public function download(string $journalSlug, DiscussionFile $file)
    {
        // Basic Access Control
        if ($file->user_id !== auth()->id() && !auth()->user()->hasRole(['Editor', 'Section Editor', 'Journal Manager', 'Admin', 'Super Admin'])) {
            abort(403);
        }
        return Storage::download($file->file_path, $file->original_name);
    }
}
