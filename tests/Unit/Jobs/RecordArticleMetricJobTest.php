<?php

/**
 * Test untuk RecordArticleMetricJob
 *
 * Memverifikasi bahwa:
 * - Job melakukan insert ke article_metrics dengan data yang benar
 * - Job memperbarui cache queue:last_processed_at setelah berhasil
 * - Job menggunakan updateOrInsert (idempotent)
 * - Job menangkap exception tanpa crash queue worker
 */

use App\Jobs\RecordArticleMetricJob;
use App\Models\Journal;
use App\Models\Submission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('RecordArticleMetricJob', function () {

    it('melakukan insert ke tabel article_metrics dengan data yang benar', function () {
        $journal    = Journal::factory()->create();
        $submission = Submission::factory()->create(['journal_id' => $journal->id]);

        $job = new RecordArticleMetricJob(
            submissionId: $submission->id,
            type:         'view',
            ipAddress:    '192.168.1.1',
            countryCode:  'ID',
            city:         'Jakarta',
            date:         '2024-01-15',
        );

        $job->handle();

        $this->assertDatabaseHas('article_metrics', [
            'submission_id' => $submission->id,
            'type'          => 'view',
            'ip_address'    => '192.168.1.1',
            'country_code'  => 'ID',
            'date'          => '2024-01-15',
        ]);
    });

    it('memperbarui cache queue:last_processed_at setelah berhasil', function () {
        $journal    = Journal::factory()->create();
        $submission = Submission::factory()->create(['journal_id' => $journal->id]);

        Cache::forget('queue:last_processed_at');

        $job = new RecordArticleMetricJob(
            submissionId: $submission->id,
            type:         'download',
            ipAddress:    null,
            countryCode:  null,
            city:         null,
            date:         now()->toDateString(),
        );

        $job->handle();

        expect(Cache::has('queue:last_processed_at'))->toBeTrue();
    });

    it('tidak membuat duplikasi jika dijalankan dua kali dengan data yang sama', function () {
        $journal    = Journal::factory()->create();
        $submission = Submission::factory()->create(['journal_id' => $journal->id]);

        $job = new RecordArticleMetricJob(
            submissionId: $submission->id,
            type:         'view',
            ipAddress:    '10.0.0.1', // IP eksplisit agar updateOrInsert bisa match
            countryCode:  'ID',
            city:         null,
            date:         '2024-01-15',
        );

        // Jalankan dua kali
        $job->handle();
        $job->handle();

        // Harus hanya ada satu baris
        $count = DB::table('article_metrics')
            ->where('submission_id', $submission->id)
            ->where('type', 'view')
            ->where('ip_address', '10.0.0.1')
            ->where('date', '2024-01-15')
            ->count();

        expect($count)->toBe(1);
    });

    it('menangkap exception dan tidak re-throw agar queue worker tidak crash', function () {
        // Submission ID yang tidak ada — akan menyebabkan foreign key violation
        $job = new RecordArticleMetricJob(
            submissionId: '00000000-0000-0000-0000-000000000000',
            type:         'view',
            ipAddress:    null,
            countryCode:  null,
            city:         null,
            date:         now()->toDateString(),
        );

        // Tidak boleh throw exception
        expect(fn() => $job->handle())->not->toThrow(\Throwable::class);
    });

});
