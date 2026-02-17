<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\SubmissionLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class CorrespondenceController extends Controller
{
    /**
     * Download the correspondence proof PDF.
     */
    public function download(Journal $journal, Submission $submission)
    {
        // Set locale to Indonesian for dates
        \Carbon\Carbon::setLocale('id');

        // Load relationships
        $submission->load(['authors', 'logs.user', 'publication', 'issue']);

        // 1. Prepare Authors and Affiliations with Superscripts
        $authorsData = [];
        $affiliationsMap = [];
        $affiliationCounter = 1;

        foreach ($submission->authors as $author) {
            $affilliation = $author->affiliation ?? null;
            $indices = [];

            if ($affilliation) {
                // Check if affiliation already indexed
                if (!isset($affiliationsMap[$affilliation])) {
                    $affiliationsMap[$affilliation] = $affiliationCounter++;
                }
                $indices[] = $affiliationsMap[$affilliation];
            }

            $authorsData[] = [
                'name' => $author->name,
                'indices' => $indices,
                'is_corresponding' => $author->is_primary_contact, // Optional: mark corresponding if needed
            ];
        }

        // 2. Filter and map logs (Oldest to Newest)
        $logs = $submission->logs
            ->sortBy('created_at') // Sort Oldest to Newest
            ->map(function ($log) {
                $description = $this->mapLogToDescription($log);
                
                if (!$description) {
                    return null;
                }

                return [
                    'event_type' => $log->event_type,
                    'description' => $description,
                    'date' => $log->created_at->isoFormat('D MMMM Y'), // Indonesian Format
                    'original_date' => $log->created_at, // Keep for debugging if needed
                ];
            })
            ->filter()
            ->values();

        // 3. Prepare data for view
        $data = [
            'journal' => $journal,
            'submission' => $submission,
            'authorsData' => $authorsData,
            'affiliationsList' => array_flip($affiliationsMap), // [1 => 'Univ A', 2 => 'Univ B']
            'logs' => $logs,
            'doi' => $submission->publication?->doi ? 'https://doi.org/' . $submission->publication->doi : null,
            'issue_vol' => $submission->issue ? $submission->issue->volume : null,
            'issue_no' => $submission->issue ? $submission->issue->number : null,
            'issue_year' => $submission->issue?->year ?? now()->year,
            'published_at' => $submission->published_at ? $submission->published_at->isoFormat('D MMMM Y') : '-',
            'submitted_at' => $submission->submitted_at ? $submission->submitted_at->isoFormat('D MMMM Y') : '-',
            'live_url' => route('journal.public.article', ['journal' => $journal->slug, 'article' => $submission->slug]),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('pdfs.correspondence', $data);
        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('Correspondence_Proof_' . $submission->slug . '.pdf');
    }

    /**
     * Map submission log event type to formal Indonesian description.
     */
    private function mapLogToDescription($log): ?string
    {
        $eventType = $log->event_type;
        $title = $log->title ?? '';

        return match ($eventType) {
            'submission_created' => 'Bukti konfirmasi submit artikel dan artikel yang disubmit',
            'editor_assigned' => 'Preliminary inspection',
            'reviewer_assigned' => 'Bukti konfirmasi kirim naskah ke reviewer',
            
            // Handle decisions based on metadata or title
            'decision_made' => $this->mapDecisionLog($log),
            
            // Handle stage changes (e.g. to Production)
            'stage_changed' => $this->mapStageChange($log),
            
            // Other events
            'revision_submitted' => 'Bukti konfirmasi submit revisi, respon kepada reviewer, dan artikel yang diresubmit',
            'payment_confirmed' => 'Konfirmasi Pembayaran APC diterima',
            'production_version_created' => 'Masuk Galley Persiapan Produksi dan persetujuan Naskah Akhir sebelum publikasi',
            'submission_published', 'published' => 'Publikasi',
            
            default => null,
        };
    }

    /**
     * Map decision logs based on metadata/title.
     */
    private function mapDecisionLog($log): ?string
    {
        $payload = $log->payload ?? [];
        $decision = $payload['decision'] ?? null;
        $title = strtolower($log->title ?? '');

        if ($decision === 'request_revisions' || str_contains($title, 'revision')) {
            return 'Permintaan Minor/Mayor Revisi';
        }
        
        if ($decision === 'accept' || str_contains($title, 'accept')) {
            return 'Bukti konfirmasi artikel accepted';
        }

        return null;
    }

    /**
     * Map stage change logs.
     */
    private function mapStageChange($log): ?string
    {
        $payload = $log->payload ?? [];
        $toStage = $payload['to_stage'] ?? null;
        $title = strtolower($log->title ?? '');

        // Stage 4 is Production
        if ($toStage == 4 || str_contains($title, 'production')) {
            return 'Masuk Galley Persiapan Produksi dan persetujuan Naskah Akhir sebelum publikasi';
        }

        return null;
    }
}
