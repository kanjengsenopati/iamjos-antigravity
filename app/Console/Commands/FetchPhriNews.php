<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\Article;

class FetchPhriNews extends Command
{
    protected $signature = 'fetch:phri-news {period=1d}';
    protected $description = 'Fetch news from PHRI API and store into the database';

    public function handle()
    {
        $period = $this->argument('period');
        $allowed = ['1d', '1w', '1m', '1y'];
        if (!in_array($period, $allowed)) {
            $this->error("Invalid period format. Use: 1d, 1w, 1m, 1y");
            return;
        }

        $url = rtrim(env('NEWS_URL'), '/') . "/{$period}";
        $response = Http::get($url);

        if (!$response->successful()) {
            $this->error("Gagal fetch data dari PHRI.");
            return;
        }

        $articles = $response->json();

        foreach ($articles as $item) {
            // Cek apakah artikel sudah ada berdasarkan external_id
            $exists = Article::where('external_id', $item['id'])->exists();
            if ($exists) {
                $this->line("Lewat: {$item['title']}");
                continue;
            }

            Article::create([
                'external_id'  => $item['id'],
                'title'        => $item['title'],
                'slug'         => Str::slug($item['title']),
                'source'       => $item['source'],
                'image'        => $item['image'] ?? null,
                'summary'      => Str::limit(strip_tags($item['body']), 200),
                'body'         => $item['body'],
                'published_at' => $item['publish_at'],
            ]);

            $this->info("Simpan: {$item['title']}");
        }

        $this->info("Selesai fetch & simpan data berita PHRI.");
    }
}
