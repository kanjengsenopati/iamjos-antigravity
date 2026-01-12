<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PhriRegencyService;

class SyncPhriRegencies extends Command
{
    protected $signature = 'phri:sync-regencies';
    protected $description = 'Sinkron data kabupaten/kota dari API PHRI ke tabel regencies';

    public function handle(PhriRegencyService $svc): int
    {
        try {
            $result = $svc->sync();

            if (!empty($result['not_modified'])) {
                $this->info('Tidak ada perubahan (304 Not Modified).');
                return self::SUCCESS;
            }

            $this->info(sprintf(
                'Selesai. Inserted: %d, Updated: %d, Mapped province_id: %d',
                $result['inserted'] ?? 0,
                $result['updated'] ?? 0,
                $result['mapped'] ?? 0,
            ));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal sinkron: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
