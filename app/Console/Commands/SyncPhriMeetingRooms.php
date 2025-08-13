<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PhriMeetingRoomService;

class SyncPhriMeetingRooms extends Command
{
    protected $signature = 'phri:sync-meetingrooms';
    protected $description = 'Sinkron data meeting rooms dari API PHRI ke tabel meeting_*';

    public function handle(PhriMeetingRoomService $svc): int
    {
        try {
            $res = $svc->sync();

            if (!empty($res['not_modified'])) {
                $this->info('Tidak ada perubahan (304 Not Modified).');
                return self::SUCCESS;
            }

            $this->info(sprintf(
                'Selesai. Venues: +%d / ~%d diperbarui, Rooms diproses: %d, Layouts diproses: %d',
                $res['venues_inserted'] ?? 0,
                $res['venues_updated'] ?? 0,
                $res['rooms_upserted']  ?? 0,
                $res['layouts_upserted'] ?? 0
            ));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gagal sinkron: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
