<?php

namespace App\Http\Controllers;

use App\Models\ReviewForm;
use App\Models\ReviewFormElement;
use App\Enums\ReviewFormElementType;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewFormElementController extends Controller
{
    /**
     * Display form builder interface.
     */
    public function builder(string $journal, string $reviewFormId): View
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $reviewForm = ReviewForm::with(['elements' => function ($query) {
            $query->ordered();
        }])->findOrFail($reviewFormId);

        if ($reviewForm->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        $elementTypes = ReviewFormElementType::toArray();

        return view('admin.journals.review-forms.builder', compact('reviewForm', 'elementTypes', 'currentJournal'))->with('journal', $currentJournal);
    }

    /**
     * Store a new form element.
     */
    public function store(Request $request, string $journal, string $reviewFormId): RedirectResponse
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
            'element_type' => 'required|string|in:text,textarea,checkbox,radio,select,rating',
            'question' => 'required|string|max:1000',
            'description' => 'nullable|string|max:2000',
            'required' => 'boolean',
            'options' => 'nullable|array',
            'options.*.value' => 'required_with:options|string',
            'options.*.label' => 'required_with:options|string',
        ]);

        // Get the highest sequence number and add 1
        $maxSequence = $reviewForm->elements()->max('sequence') ?? 0;

        $element = ReviewFormElement::create([
            'review_form_id' => $reviewForm->id,
            'element_type' => $validated['element_type'],
            'question' => $validated['question'],
            'description' => $validated['description'] ?? null,
            'required' => $request->boolean('required'),
            'options' => $validated['options'] ?? null,
            'sequence' => $maxSequence + 1,
        ]);

        return back()->with('success', 'Form element added successfully.');
    }

    /**
     * Update an existing form element.
     */
    public function update(Request $request, string $journal, string $reviewFormId, string $elementId): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $reviewForm = ReviewForm::findOrFail($reviewFormId);

        if ($reviewForm->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        $element = ReviewFormElement::findOrFail($elementId);

        if ($element->review_form_id !== $reviewForm->id) {
            abort(403, 'Unauthorized.');
        }

        $validated = $request->validate([
            'element_type' => 'required|string|in:text,textarea,checkbox,radio,select,rating',
            'question' => 'required|string|max:1000',
            'description' => 'nullable|string|max:2000',
            'required' => 'boolean',
            'options' => 'nullable|array',
            'options.*.value' => 'required_with:options|string',
            'options.*.label' => 'required_with:options|string',
        ]);

        $element->update([
            'element_type' => $validated['element_type'],
            'question' => $validated['question'],
            'description' => $validated['description'] ?? null,
            'required' => $request->boolean('required'),
            'options' => $validated['options'] ?? null,
        ]);

        return back()->with('success', 'Form element updated successfully.');
    }

    /**
     * Delete a form element.
     */
    public function destroy(string $journal, string $reviewFormId, string $elementId): RedirectResponse
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $reviewForm = ReviewForm::findOrFail($reviewFormId);

        if ($reviewForm->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        $element = ReviewFormElement::findOrFail($elementId);

        if ($element->review_form_id !== $reviewForm->id) {
            abort(403, 'Unauthorized.');
        }

        // Check if element has responses
        if ($element->getResponseCount() > 0) {
            return back()->with('error', 'Cannot delete element with existing responses.');
        }

        $element->delete();

        return back()->with('success', 'Form element deleted successfully.');
    }

    /**
     * Reorder form elements.
     */
    public function reorder(Request $request, string $journal, string $reviewFormId): RedirectResponse
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
            'elements' => 'required|array',
            'elements.*.id' => 'required|uuid|exists:review_form_elements,id',
            'elements.*.sequence' => 'required|integer|min:0',
        ]);

        foreach ($validated['elements'] as $elementData) {
            $element = ReviewFormElement::find($elementData['id']);

            if ($element && $element->review_form_id === $reviewForm->id) {
                $element->update(['sequence' => $elementData['sequence']]);
            }
        }

        return back()->with('success', 'Elements reordered successfully.');
    }

    /**
     * Preview form.
     */
    public function preview(string $journal, string $reviewFormId): View
    {
        $currentJournal = current_journal();

        if (!$currentJournal) {
            abort(404, 'Journal not found.');
        }

        $reviewForm = ReviewForm::with(['elements' => function ($query) {
            $query->ordered();
        }])->findOrFail($reviewFormId);

        if ($reviewForm->journal_id !== $currentJournal->id) {
            abort(403, 'Unauthorized.');
        }

        return view('admin.journals.review-forms.preview', compact('reviewForm', 'currentJournal'))->with('journal', $currentJournal);
    }
}
