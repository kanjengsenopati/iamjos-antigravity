<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function logHistory($id)
    {
        $submission = Submission::with(['activityLogs.user'])->findOrFail($id);

        // Return just the partial HTML, not the full page layout
        return view('admin.submissions.partials.log-modal-content', compact('submission'));
    }
}