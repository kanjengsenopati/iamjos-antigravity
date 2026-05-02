<?php

namespace App\Observers;

use App\Models\Publication;

use App\Jobs\DepositCrossrefJob;

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
    }

    protected function checkCrossrefDeposit(Publication $publication): void
    {
        if ($publication->status === Publication::STATUS_PUBLISHED) {
            $journal = $publication->submission->journal;
            if ($journal && $journal->getSetting('crossref_automatic_deposit')) {
                DepositCrossrefJob::dispatch([$publication->submission_id], $journal);
            }
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
