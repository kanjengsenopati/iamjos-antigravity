<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class SubmissionFileController extends Controller
{
    /**
     * Upload a new file to submission.
     */
    public function store(Request $request, Submission $submission): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $submission);

        $validated = $request->validate([
            'file' => 'required|file|mimes:doc,docx,pdf,odt,rtf|max:20480', // 20MB max
            'file_type' => 'required|in:manuscript,revision,supplementary,galley',
        ]);

        $file = $request->file('file');
        $user = auth()->user();

        // Determine version number
        $version = SubmissionFile::where('submission_id', $submission->id)
            ->where('file_type', $validated['file_type'])
            ->max('version') + 1;

        // Store file
        $path = $file->store("submissions/{$submission->id}", 'local');

        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'uploaded_by' => $user->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_type' => $validated['file_type'],
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'version' => $version,
            'stage' => $submission->stage,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully.',
                'file' => $submissionFile,
            ]);
        }

        return back()->with('success', 'File uploaded successfully.');
    }

    /**
     * Download a file.
     */
    public function download(SubmissionFile $file)
    {
        // Check if user can access this file
        $submission = $file->submission;
        $user = auth()->user();

        // Author can download own files
        // Editor/Admin can download all files
        // Reviewer can download manuscript files for assigned submissions
        $canDownload = false;

        if ($submission->user_id === $user->id) {
            $canDownload = true;
        } elseif ($user->hasAnyRole(['Editor', 'Admin', 'Super Admin'])) {
            $canDownload = true;
        } elseif ($user->hasRole('Reviewer')) {
            $canDownload = $submission->reviewAssignments()
                ->where('reviewer_id', $user->id)
                ->exists();
        }

        if (!$canDownload) {
            abort(403, 'You do not have permission to download this file.');
        }

        if (!Storage::disk('local')->exists($file->file_path)) {
            abort(404, 'File not found.');
        }

        return Storage::disk('local')->download($file->file_path, $file->file_name);
    }

    /**
     * Preview a file using Google Docs Viewer.
     * For local storage, we need to create a temporary signed URL.
     */
    public function preview(SubmissionFile $file)
    {
        // Same access check as download
        $submission = $file->submission;
        $user = auth()->user();

        $canView = false;

        if ($submission->user_id === $user->id) {
            $canView = true;
        } elseif ($user->hasAnyRole(['Editor', 'Section Editor', 'Admin', 'Super Admin', 'Journal Manager'])) {
            $canView = true;
        } elseif ($user->hasRole('Reviewer')) {
            $canView = $submission->reviewAssignments()
                ->where('reviewer_id', $user->id)
                ->exists();
        }

        if (!$canView) {
            abort(403, 'You do not have permission to view this file.');
        }

        if (!Storage::disk('local')->exists($file->file_path)) {
            abort(404, 'File not found.');
        }

        // Check if file is viewable (PDF, DOC, DOCX, etc.)
        $viewableExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp'];
        $extension = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));

        if (!in_array($extension, $viewableExtensions)) {
            // Not viewable, redirect to download
            return redirect()->route('files.download', $file);
        }

        // For local storage, we need to generate a temporary public URL
        // Option 1: If using S3 or public storage, use URL directly
        // Option 2: For local storage, create a signed route

        // Generate a signed URL that's valid for 1 hour
        $signedUrl = \URL::temporarySignedRoute(
            'files.serve',
            now()->addHour(),
            ['file' => $file->id]
        );

        // Encode the URL for Google Docs Viewer
        $googleDocsUrl = 'https://docs.google.com/gview?url=' . urlencode($signedUrl) . '&embedded=true';

        return view('submissions.file-preview', [
            'file' => $file,
            'journal' => $submission->journal,
            'journalSlug' => $submission->journal->slug,
            'previewUrl' => $googleDocsUrl,
            'downloadUrl' => route('files.download', $file),
        ]);
    }

    /**
     * Serve a file for preview (temporary signed URL).
     */
    public function serve(Request $request, SubmissionFile $file)
    {
        // The signed URL middleware handles expiry validation
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired link.');
        }

        if (!Storage::disk('local')->exists($file->file_path)) {
            abort(404, 'File not found.');
        }

        // Return the file with proper headers for inline viewing
        $mimeType = $file->mime_type ?? Storage::disk('local')->mimeType($file->file_path);

        return response()->file(
            Storage::disk('local')->path($file->file_path),
            [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $file->file_name . '"',
            ]
        );
    }

    /**
     * Delete a file.
     */
    public function destroy(SubmissionFile $file): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $file->submission);

        // Only allow deletion of own uploads or by editor
        $user = auth()->user();
        if ($file->uploaded_by !== $user->id && !$user->hasAnyRole(['Editor', 'Admin', 'Super Admin'])) {
            abort(403, 'You cannot delete this file.');
        }

        // Delete from storage
        if (Storage::disk('local')->exists($file->file_path)) {
            Storage::disk('local')->delete($file->file_path);
        }

        $file->delete();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully.',
            ]);
        }

        return back()->with('success', 'File deleted successfully.');
    }
}
