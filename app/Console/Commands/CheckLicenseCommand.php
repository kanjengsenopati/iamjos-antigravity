<?php
namespace App\Console\Commands;

use App\Enums\LicenseStatus;
use App\Services\LicenseService;
use Illuminate\Console\Command;

class CheckLicenseCommand extends Command
{
    protected $signature   = 'iamjos:license:check {--refresh : Hapus cache dan ambil status terbaru dari KAMPUS}';
    protected $description = 'Periksa status lisensi instance IAMJOS saat ini';

    public function handle(LicenseService $service): int
    {
        if ($this->option('refresh')) {
            $service->clearCache();
            $this->line('Cache lisensi telah dihapus. Mengambil status terbaru...');
        }

        $status = $service->getStatus();

        // Sensor license key — tampilkan hanya 8 karakter pertama
        $licenseKey = config('iamjos.license_key', '');
        $maskedKey  = strlen($licenseKey) > 8
            ? substr($licenseKey, 0, 8) . '****'
            : (empty($licenseKey) ? '(tidak dikonfigurasi)' : '****');

        $this->table(
            ['Parameter', 'Nilai'],
            [
                ['Status Lisensi',          $status->label()],
                ['IAMJOS_LICENSE_KEY',       $maskedKey],
                ['IAMJOS_KAMPUS_URL',        config('iamjos.kampus_url', '(tidak dikonfigurasi)')],
                ['LICENSE_CHECK_ENABLED',    config('iamjos.license_check_enabled', false) ? 'true' : 'false'],
            ]
        );

        $color = match ($status) {
            LicenseStatus::Valid     => 'green',
            LicenseStatus::Unchecked => 'yellow',
            default                  => 'red',
        };

        $this->line("<fg={$color}>[{$status->value}] {$status->label()}</>");

        return $status->isOperational() ? self::SUCCESS : self::FAILURE;
    }
}
