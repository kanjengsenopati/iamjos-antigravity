<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use App\Models\Journal;
use App\Services\CrossrefDepositService;

class DepositCrossrefJob implements ShouldQueue
{
    use Queueable;

    public $submissionIds;
    public $journal;
    public $objectType;

    /**
     * Create a new job instance.
     */
    public function __construct($ids, Journal $journal, $objectType = 'article')
    {
        $this->submissionIds = $ids; // Could be issue IDs if objectType is 'issue'
        $this->journal = $journal;
        $this->objectType = $objectType;
    }

    /**
     * Execute the job.
     */
    public function handle(CrossrefDepositService $service): void
    {
        if ($this->objectType === 'issue') {
            $service->depositIssues($this->submissionIds, $this->journal);
        } else {
            $service->deposit($this->submissionIds, $this->journal);
        }
    }
}
