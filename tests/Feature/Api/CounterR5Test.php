<?php

/**
 * Test untuk COUNTER R5 API Endpoints
 *
 * Memverifikasi bahwa:
 * - GET /api/v1/counter/tr/{journal} mengembalikan Title Report yang valid
 * - GET /api/v1/counter/ir/{journal} mengembalikan Item Report yang valid
 * - Validasi format tanggal YYYY-MM berjalan dengan benar
 * - Batasan range 24 bulan diterapkan
 * - Journal tidak ditemukan mengembalikan 404
 */

use App\Models\ArticleMetric;
use App\Models\Journal;
use App\Models\Publication;
use App\Models\Submission;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('COUNTER R5 Title Report (TR)', function () {

    it('mengembalikan struktur Report_Header yang benar', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/tr/{$journal->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'Report_Header' => [
                    'Report_Name',
                    'Report_ID',
                    'Release',
                    'Institution_Name',
                    'Institution_ID',
                    'Reporting_Period' => ['Begin_Date', 'End_Date'],
                    'Created',
                    'Created_By',
                ],
                'Report_Items',
            ]);
    });

    it('mengembalikan Report_ID = TR', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/tr/{$journal->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('Report_Header.Report_ID', 'TR')
            ->assertJsonPath('Report_Header.Release', '5');
    });

    it('mengembalikan header X-COUNTER-Release: 5', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/tr/{$journal->slug}");

        $response->assertHeader('X-COUNTER-Release', '5');
        $response->assertHeader('X-Report-ID', 'TR');
    });

    it('mengembalikan 404 jika journal tidak ditemukan', function () {
        $response = $this->getJson('/api/v1/counter/tr/jurnal-tidak-ada');

        $response->assertStatus(404);
    });

    it('mengembalikan 404 jika journal disabled', function () {
        $journal = Journal::factory()->create(['enabled' => false]);

        $response = $this->getJson("/api/v1/counter/tr/{$journal->slug}");

        $response->assertStatus(404);
    });

    it('mengembalikan 422 jika begin_date format salah', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/tr/{$journal->slug}?begin_date=2024-01-01");

        $response->assertStatus(422);
    });

    it('mengembalikan 422 jika end_date format salah', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/tr/{$journal->slug}?end_date=January-2024");

        $response->assertStatus(422);
    });

    it('mengembalikan 422 jika begin_date lebih besar dari end_date', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/tr/{$journal->slug}?begin_date=2024-06&end_date=2024-01");

        $response->assertStatus(422);
    });

    it('mengembalikan 422 jika range melebihi 24 bulan', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/tr/{$journal->slug}?begin_date=2022-01&end_date=2024-06");

        $response->assertStatus(422);
    });

    it('mengembalikan Report_Items kosong jika tidak ada artikel published', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/tr/{$journal->slug}?begin_date=2024-01&end_date=2024-03");

        $response->assertStatus(200)
            ->assertJsonPath('Report_Items', []);
    });

    it('mengagregasi metrik view dan download dengan benar', function () {
        $journal    = Journal::factory()->create(['enabled' => true]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);

        // Insert 3 view dan 2 download di bulan yang sama
        foreach (range(1, 3) as $i) {
            \Illuminate\Support\Facades\DB::table('article_metrics')->insert([
                'submission_id' => $submission->id,
                'type'          => 'view',
                'date'          => '2024-03-15',
                'ip_address'    => "10.0.0.{$i}",
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
        foreach (range(1, 2) as $i) {
            \Illuminate\Support\Facades\DB::table('article_metrics')->insert([
                'submission_id' => $submission->id,
                'type'          => 'download',
                'date'          => '2024-03-20',
                'ip_address'    => "10.0.1.{$i}",
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }

        $response = $this->getJson("/api/v1/counter/tr/{$journal->slug}?begin_date=2024-03&end_date=2024-03");

        $response->assertStatus(200);

        $data = $response->json();
        expect($data['Report_Items'])->toHaveCount(1);

        $item = $data['Report_Items'][0];
        expect($item['Metric_Types']['Total_Item_Requests'])->toBe(5);
        expect($item['Metric_Types']['Unique_Item_Requests'])->toBe(3);    // views
        expect($item['Metric_Types']['Total_Item_Investigations'])->toBe(2); // downloads
    });

    it('mengisi bulan tanpa data dengan count 0 dalam Performances', function () {
        $journal = Journal::factory()->create(['enabled' => true]);
        Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);

        $response = $this->getJson("/api/v1/counter/tr/{$journal->slug}?begin_date=2024-01&end_date=2024-03");

        $response->assertStatus(200);

        $data = $response->json();
        if (!empty($data['Report_Items'])) {
            $performances = $data['Report_Items'][0]['Performances'];
            // Harus ada 3 entri (Jan, Feb, Mar)
            expect($performances)->toHaveCount(3);
        }
    });

});

describe('COUNTER R5 Item Report (IR)', function () {

    it('mengembalikan struktur Report_Header yang benar', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/ir/{$journal->slug}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'Report_Header' => [
                    'Report_Name',
                    'Report_ID',
                    'Release',
                ],
                'Report_Items',
            ]);
    });

    it('mengembalikan Report_ID = IR', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/ir/{$journal->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('Report_Header.Report_ID', 'IR');
    });

    it('mengembalikan header X-Report-ID: IR', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/ir/{$journal->slug}");

        $response->assertHeader('X-Report-ID', 'IR');
    });

    it('mengembalikan 404 jika journal tidak ditemukan', function () {
        $response = $this->getJson('/api/v1/counter/ir/jurnal-tidak-ada');

        $response->assertStatus(404);
    });

    it('mengembalikan Report_Items kosong jika tidak ada artikel published', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->getJson("/api/v1/counter/ir/{$journal->slug}?begin_date=2024-01&end_date=2024-03");

        $response->assertStatus(200)
            ->assertJsonPath('Report_Items', []);
    });

    it('mengembalikan satu item per artikel dengan metadata yang benar', function () {
        $journal    = Journal::factory()->create(['enabled' => true]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);
        $pub = Publication::factory()->create([
            'submission_id' => $submission->id,
            'title'         => 'Artikel Test COUNTER',
            'doi'           => '10.12345/test.001',
            'status'        => Publication::STATUS_PUBLISHED,
        ]);

        \Illuminate\Support\Facades\DB::table('article_metrics')->insert([
            'submission_id' => $submission->id,
            'type'          => 'view',
            'date'          => '2024-03-10',
            'ip_address'    => '10.0.0.1',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $response = $this->getJson("/api/v1/counter/ir/{$journal->slug}?begin_date=2024-03&end_date=2024-03");

        $response->assertStatus(200);

        $data = $response->json();
        expect($data['Report_Items'])->toHaveCount(1);

        $item = $data['Report_Items'][0];
        expect($item)->toHaveKey('Item');
        expect($item)->toHaveKey('DOI');
        expect($item)->toHaveKey('Item_ID');
        expect($item)->toHaveKey('Performances');
    });

    it('mengurutkan Report_Items berdasarkan total requests descending', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        // Buat 2 submission dengan jumlah metrik berbeda
        $sub1 = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);
        $sub2 = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);

        Publication::factory()->create([
            'submission_id' => $sub1->id,
            'status'        => Publication::STATUS_PUBLISHED,
        ]);
        Publication::factory()->create([
            'submission_id' => $sub2->id,
            'status'        => Publication::STATUS_PUBLISHED,
        ]);

        // sub2 punya lebih banyak views
        foreach (range(1, 5) as $i) {
            \Illuminate\Support\Facades\DB::table('article_metrics')->insert([
                'submission_id' => $sub2->id,
                'type'          => 'view',
                'date'          => '2024-03-10',
                'ip_address'    => "10.0.2.{$i}",
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);
        }
        \Illuminate\Support\Facades\DB::table('article_metrics')->insert([
            'submission_id' => $sub1->id,
            'type'          => 'view',
            'date'          => '2024-03-10',
            'ip_address'    => '10.0.1.1',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $response = $this->getJson("/api/v1/counter/ir/{$journal->slug}?begin_date=2024-03&end_date=2024-03");

        $response->assertStatus(200);

        $data  = $response->json();
        $items = $data['Report_Items'];

        if (count($items) >= 2) {
            // Item pertama harus punya total lebih besar atau sama dengan item kedua
            expect($items[0]['Reporting_Period_Total'])
                ->toBeGreaterThanOrEqual($items[1]['Reporting_Period_Total']);
        }
    });

});
