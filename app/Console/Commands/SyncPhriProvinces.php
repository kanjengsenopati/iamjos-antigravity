<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PhriProvinceService;

class SyncPhriProvinces extends Command
{
    protected $signature = 'phri:sync-provinces';
    protected $description = 'Sinkron data provinsi dari API PHRI ke tabel provinces';

    public function handle(PhriProvinceService $svc): int
    {
        try {
            $result = $svc->sync();

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
            $this->error('Gagal sinkron: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
