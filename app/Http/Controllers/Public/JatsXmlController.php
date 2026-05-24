<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Submission;
use App\Services\JatsXmlService;
use Illuminate\Http\Response;

class JatsXmlController extends Controller
{
    public function __construct(
        private readonly JatsXmlService $jatsService,
    ) {}

    /**
     * Route publik: GET /{journal}/article/{article}/jats
     * Hanya untuk submission yang sudah published.
     */
    public function article(string $journalSlug, mixed $article): Response
    {
        $journal = Journal::where('slug', $journalSlug)->where('enabled', true)->first();
        if (!$journal) {
            abort(404);
        }

        $submission = Submission::where('journal_id', $journal->id)
            ->where('seq_id', $article)
            ->first();

        if (!$submission) {
            abort(404);
        }

        if ($submission->status !== Submission::STATUS_PUBLISHED) {
            abort(404);
        }

        if (!$submission->currentPublication) {
            abort(404);
        }

        $submission->load(['currentPublication.authors', 'issue', 'journal', 'section']);

        $xml = $this->jatsService->generate($submission);

        return $this->xmlResponse($xml, "{$journal->slug}-{$submission->seq_id}.xml");
    }

    /**
     * Route admin: GET /{journal}/workflow/{submission}/jats
     * Preview dari halaman workflow, termasuk submission belum published.
     */
    public function workflowPreview(string $journalSlug, Submission $submission): Response
    {
        $journal = Journal::where('slug', $journalSlug)->where('enabled', true)->first();
        if (!$journal) {
            abort(404);
        }

        if ($submission->journal_id !== $journal->id) {
            abort(404);
        }

        $submission->load(['currentPublication.authors', 'issue', 'journal', 'section']);

        $xml = $this->jatsService->generate($submission);

        return $this->xmlResponse($xml, "{$journal->slug}-{$submission->seq_id}.xml");
    }

    private function xmlResponse(string $xml, string $filename): Response
    {
        return response($xml, 200, [
            'Content-Type'        => 'application/xml; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
