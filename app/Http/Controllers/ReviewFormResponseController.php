<?php

namespace App\Http\Controllers;

use App\Models\ReviewAssignment;
use App\Models\ReviewFormResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ReviewFormResponseController extends Controller
{
    /**
     * Display the review form for reviewer to fill
     */
    public function show(string $assignmentSlug): View
    {
        $assignment = ReviewAssignment::with([
            'reviewForm.elements' => function ($query) {
                $query->ordered();
            },
            'formResponses',
            'submission.journal'
        ])->where('slug', $assignmentSlug)->firstOrFail();

        // Check authorization
        if (auth()->id() !== $assignment->reviewer_id) {
            abort(403, 'Unauthorized to access this review assignment.');
        }

        // Check if assignment has a form
        if (!$assignment->hasReviewForm()) {
            return redirect()->route('reviewer.assignment.show', $assignmentSlug)
                ->with('info', 'This review does not have a structured form.');
        }

        $reviewForm = $assignment->reviewForm;
        
        // Get existing responses
        $existingResponses = $assignment->formResponses->keyBy('review_form_element_id');

        return view('reviewer.review-form', compact('assignment', 'reviewForm', 'existingResponses'));
    }

    /**
     * Save or update form responses
     */
    public function store(Request $request, string $assignmentSlug): RedirectResponse
    {
        $assignment = ReviewAssignment::with('reviewForm.elements')
            ->where('slug', $assignmentSlug)
            ->firstOrFail();

        // Check authorization
        if (auth()->id() !== $assignment->reviewer_id) {
            abort(403, 'Unauthorized.');
        }

        // Check if assignment has a form
        if (!$assignment->hasReviewForm()) {
            return back()->with('error', 'This review does not have a form.');
        }

        $reviewForm = $assignment->reviewForm;

        // Validate responses
        $rules = [];
        $messages = [];

        foreach ($reviewForm->elements as $element) {
            $fieldName = "responses.{$element->id}";
            
            if ($element->required) {
                $rules[$fieldName] = 'required';
                $messages["{$fieldName}.required"] = "The question \"{$element->question}\" is required.";
            }

            // Type-specific validation
            if ($element->element_type->value === 'checkbox') {
                $rules[$fieldName] = ($element->required ? 'required|' : '') . 'array';
            } elseif ($element->element_type->value === 'rating') {
                $config = $element->getRatingConfig();
                $rules[$fieldName] = ($element->required ? 'required|' : '') . "integer|min:{$config['min']}|max:{$config['max']}";
            }
        }

        $validated = $request->validate($rules, $messages);

        // Save responses
        DB::transaction(function () use ($assignment, $reviewForm, $request) {
            foreach ($reviewForm->elements as $element) {
                $value = $request->input("responses.{$element->id}");

                // Skip if empty and not required
                if (empty($value) && !$element->required) {
                    continue;
                }

                // Format value based on element type
                if ($element->element_type->value === 'checkbox' && is_array($value)) {
                    $value = json_encode($value);
                } else {
                    $value = is_array($value) ? json_encode($value) : $value;
                }

                // Update or create response
                ReviewFormResponse::updateOrCreate(
                    [
                        'review_assignment_id' => $assignment->id,
                        'review_form_element_id' => $element->id,
                    ],
                    [
                        'response_value' => $value,
                    ]
                );
            }

            // Update review form response count
            $assignment->reviewForm->increment('response_count');
        });

        // Determine if this is draft or final submission
        $isDraft = $request->has('save_draft');

        if ($isDraft) {
            return back()->with('success', 'Form responses saved as draft.');
        }

        // If final submission, redirect to main review page
        return redirect()->route('reviewer.assignment.show', $assignmentSlug)
            ->with('success', 'Form responses submitted successfully.');
    }

    /**
     * Display form responses for editor/admin
     */
    public function showResponses(string $assignmentSlug): View
    {
        $assignment = ReviewAssignment::with([
            'reviewForm.elements' => function ($query) {
                $query->ordered();
            },
            'formResponses.element',
            'reviewer',
            'submission'
        ])->where('slug', $assignmentSlug)->firstOrFail();

        // Check if user has permission to view
        // Editor/Admin can view all responses
        $user = auth()->user();
        if (!$user->hasAnyRole(['Editor', 'Admin', 'Super Admin', 'Journal Manager'])) {
            abort(403, 'Unauthorized to view responses.');
        }

        return view('admin.reviews.form-responses', compact('assignment'));
    }

    /**
     * Export form responses to CSV
     */
    public function export(string $assignmentSlug)
    {
        $assignment = ReviewAssignment::with([
            'reviewForm.elements' => function ($query) {
                $query->ordered();
            },
            'formResponses.element',
            'reviewer'
        ])->where('slug', $assignmentSlug)->firstOrFail();

        // Check permission
        $user = auth()->user();
        if (!$user->hasAnyRole(['Editor', 'Admin', 'Super Admin', 'Journal Manager'])) {
            abort(403, 'Unauthorized.');
        }

        $responses = $assignment->formResponses;
        $filename = "review-form-responses-{$assignmentSlug}-" . now()->format('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($assignment, $responses) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, ['Reviewer', 'Question', 'Response', 'Type', 'Submitted At']);

            // Data rows
            foreach ($responses as $response) {
                fputcsv($file, [
                    $assignment->reviewer->name ?? 'Unknown',
                    $response->element->question ?? 'N/A',
                    $response->getDisplayLabel(),
                    $response->element->element_type->label() ?? 'N/A',
                    $response->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
