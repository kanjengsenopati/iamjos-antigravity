<?php

namespace App\Console\Commands;

use App\Models\Article;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Stichoza\GoogleTranslate\GoogleTranslate;

class FetchPhriNews extends Command
{
    protected $signature = 'fetch:phri-news {period=1w}';
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
            // Hindari duplikat
            if (in_array($item['id'] ?? null, $existingIds, true)) {
                continue;
            }

            // 1) Normalisasi & validasi data sumber
            $rawTitle = Arr::get($item, 'title');         // wajib
            $rawBody  = Arr::get($item, 'body');          // wajib
            $source   = Arr::get($item, 'source');
            $image    = Arr::get($item, 'image');
            $pubAt    = Arr::get($item, 'publish_at');

            if (empty($rawTitle) || empty($rawBody)) {
                $this->warn("⚠️ Skip ID " . Arr::get($item, 'id') . " karena title/body kosong.");
                continue;
            }

            // 2) Rapikan ringkasan (hapus HTML, trim spasi berlebih), lalu limit 200 char
            $summaryPlain = Str::limit(
                preg_replace('/\s+/u', ' ', trim(strip_tags($rawBody))) ?? '',
                200
            );

            // 3) Translate dengan proteksi error (dan skip kalau teks kosong)
            $title_en   = $this->translateSafe($rawTitle, 'en');            // dari sumber yang sama dengan title asli
            $summary_en = $summaryPlain !== '' ? $this->translateSafe($summaryPlain, 'en') : null;
            $body_en    = $this->translateSafe($rawBody, 'en');

            // 4) Susun payload insert
            $articlesToInsert[] = [
                'id'           => Str::uuid(),
                'external_id'  => Arr::get($item, 'id'),
                'title'        => $rawTitle,
                'title_en'     => $title_en,
                'slug'         => Str::slug($rawTitle) . '-' . Str::random(5), // unik, berbasis judul yang sama
                'source'       => $source,
                'image'        => $image ?: null,
                'summary'      => $summaryPlain,
                'summary_en'   => $summary_en,
                'body'         => $rawBody,   // simpan HTML asli
                'body_en'      => $body_en,   // hasil translate
                'published_at' => $pubAt,
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

    /**
     * Translate dengan proteksi error & fallback ke teks asli bila gagal.
     * (Opsional) aktifkan cache agar hemat biaya/rate limit.
     */
    private function translateSafe(?string $text, string $to = 'en'): ?string
    {
        $text = $text ?? '';
        if ($text === '') {
            return null;
        }

        try {
            return GoogleTranslate::trans($text, $to);
        } catch (\Throwable $e) {
            $this->warn("Translate gagal: " . $e->getMessage());
            return $text; // fallback ke teks asli supaya insert tetap jalan
        }
    }
}
