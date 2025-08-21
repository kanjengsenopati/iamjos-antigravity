<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PhriEventService;

class FetchEvents extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch events from PHRI API and store to database';

    /**
     * Execute the console command.
     */
    public function handle(PhriEventService $service): int
    {
        try {
            $result = $service->sync();

            if (!empty($result['not_modified'])) {
                $this->info('Tidak ada perubahan (304 Not Modified).');
                return self::SUCCESS;
            }

            $this->info(sprintf(
                'Selesai. Inserted: %d, Updated: %d',
                $result['inserted'] ?? 0,
                $result['updated'] ?? 0
            ));

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal fetch events: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
