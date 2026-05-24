<?php

/**
 * Test untuk JatsXmlController
 *
 * Memverifikasi bahwa:
 * - Route publik mengembalikan XML untuk artikel published
 * - Route publik mengembalikan 404 untuk artikel belum published
 * - Route publik mengembalikan 404 untuk seq_id yang tidak ada
 * - Header Content-Type dan Content-Disposition benar
 * - Route admin (workflowPreview) dapat mengakses submission belum published
 */

use App\Models\Journal;
use App\Models\Publication;
use App\Models\Submission;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

describe('JatsXmlController — Route Publik', function () {

    it('mengembalikan 200 dan Content-Type application/xml untuk artikel published', function () {
        $journal    = Journal::factory()->create(['enabled' => true]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);
        $pub = Publication::factory()->create([
            'submission_id' => $submission->id,
            'status'        => Publication::STATUS_PUBLISHED,
        ]);

        $response = $this->get(route('journal.article.jats', [
            'journal' => $journal->slug,
            'article' => $submission->seq_id,
        ]));

        $response->assertStatus(200);
        expect($response->headers->get('Content-Type'))->toContain('application/xml');
    });

    it('mengembalikan header Content-Disposition dengan nama file yang benar', function () {
        $journal    = Journal::factory()->create(['enabled' => true, 'slug' => 'jik-test']);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);
        $pub = Publication::factory()->create([
            'submission_id' => $submission->id,
            'status'        => Publication::STATUS_PUBLISHED,
        ]);

        $response = $this->get(route('journal.article.jats', [
            'journal' => $journal->slug,
            'article' => $submission->seq_id,
        ]));

        $response->assertStatus(200);
        $disposition = $response->headers->get('Content-Disposition');
        expect($disposition)->toContain('attachment');
        expect($disposition)->toContain('.xml');
    });

    it('mengembalikan XML yang well-formed', function () {
        $journal    = Journal::factory()->create(['enabled' => true]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);
        $pub = Publication::factory()->create([
            'submission_id' => $submission->id,
            'status'        => Publication::STATUS_PUBLISHED,
        ]);

        $response = $this->get(route('journal.article.jats', [
            'journal' => $journal->slug,
            'article' => $submission->seq_id,
        ]));

        $response->assertStatus(200);

        $dom    = new DOMDocument();
        $result = $dom->loadXML($response->getContent());
        expect($result)->toBeTrue('Response harus berupa XML yang well-formed');
    });

    it('mengembalikan 404 untuk artikel yang belum published', function () {
        $journal    = Journal::factory()->create(['enabled' => true]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_SUBMITTED, // bukan published
        ]);

        $response = $this->get(route('journal.article.jats', [
            'journal' => $journal->slug,
            'article' => $submission->seq_id,
        ]));

        $response->assertStatus(404);
    });

    it('mengembalikan 404 untuk seq_id yang tidak ada', function () {
        $journal = Journal::factory()->create(['enabled' => true]);

        $response = $this->get(route('journal.article.jats', [
            'journal' => $journal->slug,
            'article' => 99999,
        ]));

        $response->assertStatus(404);
    });

    it('mengembalikan 404 untuk journal yang tidak ditemukan', function () {
        $response = $this->get(route('journal.article.jats', [
            'journal' => 'jurnal-tidak-ada',
            'article' => 1,
        ]));

        $response->assertStatus(404);
    });

    it('mengembalikan 404 untuk journal yang disabled', function () {
        $journal    = Journal::factory()->create(['enabled' => false]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);

        $response = $this->get(route('journal.article.jats', [
            'journal' => $journal->slug,
            'article' => $submission->seq_id,
        ]));

        $response->assertStatus(404);
    });

    it('mengembalikan 404 jika submission tidak punya currentPublication', function () {
        $journal    = Journal::factory()->create(['enabled' => true]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
            // Tidak ada publication yang dibuat — currentPublication akan null
        ]);

        $response = $this->get(route('journal.article.jats', [
            'journal' => $journal->slug,
            'article' => $submission->seq_id,
        ]));

        $response->assertStatus(404);
    });

});

describe('JatsXmlController — Route Admin (workflowPreview)', function () {

    it('mengembalikan 200 untuk submission yang belum published (preview mode)', function () {
        $journal    = Journal::factory()->create(['enabled' => true]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_SUBMITTED, // belum published
        ]);
        $pub = Publication::factory()->create([
            'submission_id' => $submission->id,
        ]);

        // Login sebagai editor
        $editor = User::factory()->create();
        $editor->assignRole('Editor');

        $response = $this->actingAs($editor)->get(route('journal.workflow.jats', [
            'journal'    => $journal->slug,
            'submission' => $submission->id,
        ]));

        $response->assertStatus(200);
        expect($response->headers->get('Content-Type'))->toContain('application/xml');
    });

    it('mengembalikan 404 jika submission bukan milik journal yang diminta', function () {
        $journal1   = Journal::factory()->create(['enabled' => true]);
        $journal2   = Journal::factory()->create(['enabled' => true]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal2->id, // milik journal2
            'status'     => Submission::STATUS_PUBLISHED,
        ]);

        $editor = User::factory()->create();
        $editor->assignRole('Editor');

        $response = $this->actingAs($editor)->get(route('journal.workflow.jats', [
            'journal'    => $journal1->slug, // tapi request ke journal1
            'submission' => $submission->id,
        ]));

        $response->assertStatus(404);
    });

    it('mengembalikan 403 atau redirect untuk user tanpa role editor', function () {
        $journal    = Journal::factory()->create(['enabled' => true]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);

        $user = User::factory()->create();
        // Tidak assign role editor

        $response = $this->actingAs($user)->get(route('journal.workflow.jats', [
            'journal'    => $journal->slug,
            'submission' => $submission->id,
        ]));

        // Accept 403 or redirect
        expect($response->status())->toBeIn([403, 301, 302]);
    });

    it('mengembalikan 401 untuk guest yang mengakses route admin', function () {
        $journal    = Journal::factory()->create(['enabled' => true]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'status'     => Submission::STATUS_PUBLISHED,
        ]);

        $response = $this->get(route('journal.workflow.jats', [
            'journal'    => $journal->slug,
            'submission' => $submission->id,
        ]));

        // Redirect ke login (301/302) atau 401
        expect($response->status())->toBeIn([401, 301, 302]);
    });

});
