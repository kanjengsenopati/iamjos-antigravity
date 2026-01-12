<?php

namespace App\Console\Commands;

use App\Models\MediaCorner;
use App\Services\YouTubeSearchService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Stichoza\GoogleTranslate\GoogleTranslate;

class SyncMediaCornerPhri extends Command
{
    protected $signature = 'media:sync-phri 
                            {--pages=2 : Maksimal halaman per kata kunci (hemat kuota)} 
                            {--after= : Filter publishedAfter ISO8601, opsional} 
                            {--deactivate-missing=0 : Set 1 untuk nonaktifkan record yang tidak lagi ditemukan}';

    protected $description = 'Sinkron video YouTube terkait PHRI/perhimpunan hotel indonesia ke tabel media_corners';

    public function handle(YouTubeSearchService $yt)
    {
        $pages = (int) $this->option('pages') ?: 2;
        $after = $this->option('after');
        $deactivateMissing = (bool) $this->option('deactivate-missing');

        $terms = [
            // Singkatan & nama lengkap
            'phri',
            '"perhimpunan hotel dan restoran indonesia"',

            // Berita & informasi
            '"berita phri"',
            '"berita perhimpunan hotel dan restoran indonesia"',
            '"informasi phri"',
            '"informasi perhimpunan hotel dan restoran indonesia"',
            '"kabar phri"',
            '"kabar perhimpunan hotel dan restoran indonesia"',

            // Event & kegiatan
            '"rapat phri"',
            '"rapat perhimpunan hotel dan restoran indonesia"',
            '"konferensi phri"',
            '"konferensi perhimpunan hotel dan restoran indonesia"',
            '"seminar phri"',
            '"seminar perhimpunan hotel dan restoran indonesia"',
            '"musyawarah phri"',
            '"musyawarah perhimpunan hotel dan restoran indonesia"',

            // Pariwisata & perhotelan
            '"pariwisata phri"',
            '"pariwisata perhimpunan hotel dan restoran indonesia"',
            '"perhotelan phri"',
            '"perhotelan perhimpunan hotel dan restoran indonesia"',
            '"restoran phri"',
            '"restoran perhimpunan hotel dan restoran indonesia"'
        ];

        $this->info("Fetching from YouTube API (pages={$pages}, after=" . ($after ?: '-') . ") …");
        $videos = $yt->searchByTitleOrDescription($terms, $after, $pages);

        // Simpan semua video (update jika sudah ada, insert jika baru)
        foreach ($videos as $v) {
            $title_en = GoogleTranslate::trans($v['title'], 'en');
            $description_en = GoogleTranslate::trans($v['description'], 'en');

            MediaCorner::updateOrCreate(
                ['video_id' => $v['videoId']],
                [
                    'title'        => $v['title'],
                    'title_en'     => $title_en ?? $v['title'],
                    'description'  => $v['description'],
                    'description_en' => $description_en ?? $v['description'],
                    'channel'      => $v['channel'],
                    'published_at' => $v['publishedAt'] ? Carbon::parse($v['publishedAt']) : null,
                    'url'          => $v['url'],
                    'thumbnails'   => $v['thumbnails'], // model sudah cast ke array
                    'is_active'    => true,
                ]
            );
        }

        $this->info('Upserted: ' . count($videos) . ' videos.');

        // Opsional: Nonaktifkan yang hilang dari hasil terbaru
        if ($deactivateMissing) {
            $currentIds = collect($videos)->pluck('videoId')->all();
            $affected = MediaCorner::query()
                ->when(!empty($currentIds), fn($q) => $q->whereNotIn('video_id', $currentIds))
                ->where('is_active', true)
                ->update(['is_active' => false]);

            $this->info("Deactivated missing: {$affected}");
        }

        $this->info('Done.');
        return self::SUCCESS;
    }
}
