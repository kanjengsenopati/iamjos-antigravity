<?php

/**
 * Test untuk CounterR5Service
 *
 * Memverifikasi bahwa:
 * - titleReport() menghasilkan struktur COUNTER R5 yang benar
 * - itemReport() menghasilkan satu item per artikel
 * - Bulan tanpa data diisi dengan count 0 (zero-fill)
 * - parseDateRange() menangani format YYYY-MM dengan benar
 * - buildReport() menghasilkan Report_Header yang lengkap
 */

use App\Models\Journal;
use App\Models\Publication;
use App\Models\Submission;
use App\Services\CounterR5Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Helper: insert metrik langsung ke DB
 */
function insertMetric(string $submissionId, string $type, string $date, string $ip = '10.0.0.1'): void
{
    DB::table('article_metrics')->insert([
        'submission_id' => $submissionId,
        'type'          => $type,
        'date'          => $date,
        'ip_address'    => $ip,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);
}

describe('CounterR5Service — titleReport()', function () {

    beforeEach(function () {
        $this->service = new CounterR5Service();
    });

    it('menghasilkan Report_Header dengan field wajib', function () {
        $journal = Journal::factory()->create();

        $report = $this->service->titleReport($journal, '2024-01', '2024-03');

        expect($report)->toHaveKey('Report_Header');
        expect($report['Report_Header'])->toHaveKeys([
            'Report_Name', 'Report_ID', 'Release',
            'Institution_Name', 'Institution_ID',
            'Reporting_Period', 'Created', 'Created_By',
        ]);
        expect($report['Report_Header']['Report_ID'])->toBe('TR');
        expect($report['Report_Header']['Release'])->toBe('5');
    });

    it('menghasilkan Report_Items kosong jika tidak ada submission published', function () {
        $journal = Journal::factory()->create();

        $report = $this->service->titleReport($journal, '2024-01', '2024-03');

        expect($report['Report_Items'])->toBeArray()->toBeEmpty();
    });

    it('mengagregasi view dan download dengan benar', function () {
        $journal    = Journal::factory()->create();
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);

        insertMetric($submission->id, 'view',     '2024-02-10', '10.0.0.1');
        insertMetric($submission->id, 'view',     '2024-02-11', '10.0.0.2');
        insertMetric($submission->id, 'download', '2024-02-12', '10.0.0.3');

        $report = $this->service->titleReport($journal, '2024-02', '2024-02');

        expect($report['Report_Items'])->toHaveCount(1);

        $item = $report['Report_Items'][0];
        expect($item['Metric_Types']['Total_Item_Requests'])->toBe(3);
        expect($item['Metric_Types']['Unique_Item_Requests'])->toBe(2);      // views
        expect($item['Metric_Types']['Total_Item_Investigations'])->toBe(1); // downloads
        expect($item['Reporting_Period_Total'])->toBe(3);
    });

    it('mengisi bulan tanpa data dengan count 0 (zero-fill)', function () {
        $journal    = Journal::factory()->create();
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);

        // Hanya ada data di Februari, bukan Januari dan Maret
        insertMetric($submission->id, 'view', '2024-02-15');

        $report = $this->service->titleReport($journal, '2024-01', '2024-03');

        $performances = $report['Report_Items'][0]['Performances'];

        // Harus ada 3 entri (Jan, Feb, Mar)
        expect($performances)->toHaveCount(3);

        // Januari harus 0
        $jan = collect($performances)->first(fn($p) => str_starts_with($p['Period']['Begin_Date'], '2024-01'));
        expect($jan['Instances'][0]['Count'])->toBe(0); // Total_Item_Requests

        // Februari harus 1
        $feb = collect($performances)->first(fn($p) => str_starts_with($p['Period']['Begin_Date'], '2024-02'));
        expect($feb['Instances'][0]['Count'])->toBe(1);
    });

    it('mengisi Reporting_Period dengan tanggal yang benar', function () {
        $journal = Journal::factory()->create();

        $report = $this->service->titleReport($journal, '2024-01', '2024-03');

        expect($report['Report_Header']['Reporting_Period']['Begin_Date'])->toBe('2024-01-01');
        expect($report['Report_Header']['Reporting_Period']['End_Date'])->toBe('2024-03-31');
    });

    it('tidak menghitung submission yang belum published', function () {
        $journal = Journal::factory()->create();

        $draft = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_SUBMITTED, // bukan published
        ]);

        insertMetric($draft->id, 'view', '2024-02-10');

        $report = $this->service->titleReport($journal, '2024-02', '2024-02');

        expect($report['Report_Items'])->toBeEmpty();
    });

    it('tidak menghitung metrik di luar range tanggal', function () {
        $journal    = Journal::factory()->create();
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);

        // Metrik di luar range
        insertMetric($submission->id, 'view', '2023-12-31');
        insertMetric($submission->id, 'view', '2024-04-01');

        // Metrik di dalam range
        insertMetric($submission->id, 'view', '2024-02-15');

        $report = $this->service->titleReport($journal, '2024-01', '2024-03');

        $item = $report['Report_Items'][0];
        expect($item['Reporting_Period_Total'])->toBe(1);
    });

});

describe('CounterR5Service — itemReport()', function () {

    beforeEach(function () {
        $this->service = new CounterR5Service();
    });

    it('menghasilkan Report_ID = IR', function () {
        $journal = Journal::factory()->create();

        $report = $this->service->itemReport($journal, '2024-01', '2024-03');

        expect($report['Report_Header']['Report_ID'])->toBe('IR');
    });

    it('menghasilkan Report_Items kosong jika tidak ada submission published', function () {
        $journal = Journal::factory()->create();

        $report = $this->service->itemReport($journal, '2024-01', '2024-03');

        expect($report['Report_Items'])->toBeArray()->toBeEmpty();
    });

    it('menghasilkan satu item per artikel dengan metadata yang benar', function () {
        $journal    = Journal::factory()->create(['name' => 'Jurnal Test']);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);
        $pub = Publication::factory()->create([
            'submission_id' => $submission->id,
            'title'         => 'Artikel Test',
            'doi'           => '10.12345/test.001',
            'status'        => Publication::STATUS_PUBLISHED,
        ]);

        insertMetric($submission->id, 'view',     '2024-02-10');
        insertMetric($submission->id, 'download', '2024-02-11');

        $report = $this->service->itemReport($journal, '2024-02', '2024-02');

        expect($report['Report_Items'])->toHaveCount(1);

        $item = $report['Report_Items'][0];
        expect($item)->toHaveKey('Item');
        expect($item)->toHaveKey('DOI');
        expect($item)->toHaveKey('Item_ID');
        expect($item)->toHaveKey('Performances');
        expect($item['Metric_Types']['Total_Item_Requests'])->toBe(2);
    });

    it('mengurutkan items berdasarkan Reporting_Period_Total descending', function () {
        $journal = Journal::factory()->create();

        $sub1 = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);
        $sub2 = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);

        $pub1 = Publication::factory()->create([
            'submission_id' => $sub1->id,
            'status'        => Publication::STATUS_PUBLISHED,
        ]);
        $pub2 = Publication::factory()->create([
            'submission_id' => $sub2->id,
            'status'        => Publication::STATUS_PUBLISHED,
        ]);

        // sub1 punya 1 view, sub2 punya 3 views
        insertMetric($sub1->id, 'view', '2024-02-10', '10.0.1.1');
        foreach (range(1, 3) as $i) {
            insertMetric($sub2->id, 'view', '2024-02-10', "10.0.2.{$i}");
        }

        $report = $this->service->itemReport($journal, '2024-02', '2024-02');

        $items = $report['Report_Items'];
        expect($items)->toHaveCount(2);

        // Item pertama harus punya total lebih besar
        expect($items[0]['Reporting_Period_Total'])
            ->toBeGreaterThanOrEqual($items[1]['Reporting_Period_Total']);
    });

    it('menyertakan DOI dalam Item_ID jika tersedia', function () {
        $journal    = Journal::factory()->create();
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);
        $pub = Publication::factory()->create([
            'submission_id' => $submission->id,
            'doi'           => '10.99999/test.doi',
            'status'        => Publication::STATUS_PUBLISHED,
        ]);

        insertMetric($submission->id, 'view', '2024-02-10');

        $report = $this->service->itemReport($journal, '2024-02', '2024-02');

        $item    = $report['Report_Items'][0];
        $itemIds = collect($item['Item_ID']);

        $doiEntry = $itemIds->firstWhere('Type', 'DOI');
        expect($doiEntry)->not->toBeNull();
        expect($doiEntry['Value'])->toBe('10.99999/test.doi');
    });

    it('tidak menyertakan DOI dalam Item_ID jika tidak tersedia', function () {
        $journal    = Journal::factory()->create();
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);
        $pub = Publication::factory()->create([
            'submission_id' => $submission->id,
            'doi'           => null,
            'status'        => Publication::STATUS_PUBLISHED,
        ]);

        insertMetric($submission->id, 'view', '2024-02-10');

        $report = $this->service->itemReport($journal, '2024-02', '2024-02');

        $item    = $report['Report_Items'][0];
        $itemIds = collect($item['Item_ID']);

        $doiEntry = $itemIds->firstWhere('Type', 'DOI');
        expect($doiEntry)->toBeNull();
    });

});
