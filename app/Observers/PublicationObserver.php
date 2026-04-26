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
        if ($publication->wasChanged('status')) {
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
