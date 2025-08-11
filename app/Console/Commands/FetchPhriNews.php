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
            $this->error("❌ Invalid period. Gunakan salah satu dari: 1d, 1w, 1m, 1y");
            return;
        }

        $url = rtrim(env('NEWS_URL'), '/') . "/{$period}";
        $this->info("📥 Fetching data from: {$url}");

        $response = Http::withHeaders([
            'x-api-key' => env('PHRI_API_KEY'),
        ])->get($url);

        if (!$response->successful()) {
            $this->error("❌ Gagal mengakses API. Status: " . $response->status());
            return;
        }

        $responseData = $response->json();

        if (!is_array($responseData) || !isset($responseData['RECORDS']) || !is_array($responseData['RECORDS'])) {
            $this->error("❌ Response tidak sesuai format. Data: " . json_encode($responseData));
            return;
        }

        $articles = $responseData['RECORDS'];

        // Validasi tipe data
        if (!is_array($articles)) {
            $this->error("❌ Response tidak valid. Dapatkan tipe: " . gettype($articles));
            $this->line(json_encode($articles)); // Debug responsenya
            return;
        }

        // Cek jika setiap elemen adalah array (bukan int)
        if (!isset($articles[0]) || !is_array($articles[0])) {
            $this->error("❌ Format response tidak sesuai. Isi pertama bukan array.");
            $this->line(json_encode($articles));
            return;
        }

        // Ambil ID yang sudah ada di DB untuk validasi duplikat
        $existingIds = Article::whereIn('external_id', collect($articles)->pluck('id'))->pluck('external_id')->toArray();

        $articlesToInsert = [];

        foreach ($articles as $item) {
            if (in_array($item['id'], $existingIds)) continue;

            $articlesToInsert[] = [
                'id'           => Str::uuid(),
                'external_id'  => $item['id'],
                'title'        => $item['title'],
                'slug'         => Str::slug($item['title']) . '-' . Str::random(5), // slug unik
                'source'       => $item['source'],
                'image'        => $item['image'] ?? null,
                'summary'      => Str::limit(strip_tags($item['body']), 200),
                'body'         => $item['body'],
                'published_at' => $item['publish_at'],
                'created_at'   => now(),
                'updated_at'   => now(),
            ];
        }

        if (empty($articlesToInsert)) {
            $this->info("ℹ️ Semua artikel sudah ada di database.");
            return;
        }

        // Insert per 500 record
        $chunks = array_chunk($articlesToInsert, 500);
        foreach ($chunks as $chunk) {
            Article::insert($chunk);
        }

        $this->info("✅ Selesai! Total artikel baru disimpan: " . count($articlesToInsert));
    }
}
