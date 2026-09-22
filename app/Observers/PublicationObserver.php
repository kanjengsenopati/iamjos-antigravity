<?php

namespace App\Observers;

use App\Models\Publication;

use App\Jobs\DepositCrossrefJob;
use App\Jobs\ProcessCitationsJob;

class PublicationObserver
{
    /**
     * Handle the Publication "created" event.
     */
    public function created(Publication $publication): void
    {
        $this->checkCrossrefDeposit($publication);
    }

    /**
     * Handle the Publication "updated" event.
     */
    public function updated(Publication $publication): void
    {
        // Re-deposit to Crossref if status changed OR if key metadata changed in a published article
        if ($publication->wasChanged('status') || 
            ($publication->isPublished() && $publication->wasChanged(['references', 'title', 'abstract', 'keywords', 'doi', 'pages']))) {
            $this->checkCrossrefDeposit($publication);
        }

        // Process references into structured citations for better Scholar/CrossRef matching
        if ($publication->wasChanged('references') || 
            ($publication->wasChanged('status') && $publication->isPublished())) {
            ProcessCitationsJob::dispatch($publication->id);
        }
    }

    protected function checkCrossrefDeposit(Publication $publication): void
    {
        if ($publication->status !== Publication::STATUS_PUBLISHED) {
            return;
        }

        // Idempotency guard: jangan deposit ulang jika sudah submitted/active
        // Identik OJS yang cek status sebelum deposit
        if (in_array($publication->doi_status, ['submitted', 'active', 'marked'])) {
            return;
        }

        // Guard: harus punya DOI yang di-assign
        if (empty($publication->doi)) {
            return;
        }

        $journal = $publication->submission->journal;
        if ($journal && $journal->getSetting('crossref_automatic_deposit')) {
            DepositCrossrefJob::dispatch([$publication->submission_id], $journal);
        }
    }

    /**
     * Handle the Publication "deleted" event.
     */
    public function deleted(Publication $publication): void
    {
        //
    }

    /**
     * Handle the Publication "restored" event.
     */
    public function restored(Publication $publication): void
    {
        //
    }

    /**
     * Handle the Publication "force deleted" event.
     */
    public function forceDeleted(Publication $publication): void
    {
        //
    }
}
