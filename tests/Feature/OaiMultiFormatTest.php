<?php

/**
 * Test untuk OAI-PMH Multi-Format Support
 *
 * Memverifikasi bahwa:
 * - ListMetadataFormats mengembalikan oai_dc, marc21, rfc1807
 * - ListRecords dengan metadataPrefix=oai_dc berjalan
 * - ListRecords dengan metadataPrefix=marc21 berjalan
 * - ListRecords dengan metadataPrefix=rfc1807 berjalan
 * - GetRecord dengan berbagai format berjalan
 * - Format yang tidak didukung mengembalikan cannotDisseminateFormat
 * - Validasi OAI-PMH standar (badVerb, badArgument, dll.)
 */

use App\Models\Journal;
use App\Models\Publication;
use App\Models\Submission;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Helper: buat journal dengan OAI enabled dan satu artikel published
 */
function makeOaiJournal(): array
{
    $journal = Journal::factory()->create([
        'enabled'    => true,
        'enable_oai' => true,
        'slug'       => 'oai-test-' . uniqid(),
    ]);

    $submission = Submission::factory()->create([
        'journal_id' => $journal->id,
        'status'     => Submission::STATUS_PUBLISHED,
    ]);

    $pub = Publication::factory()->create([
        'submission_id' => $submission->id,
        'status'        => Publication::STATUS_PUBLISHED,
        'title'         => 'Artikel OAI Test',
    ]);

    return [$journal, $submission];
}

describe('OAI-PMH ListMetadataFormats', function () {

    it('mengembalikan tiga format: oai_dc, marc21, rfc1807', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(route('journal.oai', $journal->slug) . '?verb=ListMetadataFormats');

        $response->assertStatus(200);
        $content = $response->getContent();

        expect($content)->toContain('oai_dc');
        expect($content)->toContain('marc21');
        expect($content)->toContain('rfc1807');
    });

    it('mengembalikan Content-Type text/xml', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(route('journal.oai', $journal->slug) . '?verb=ListMetadataFormats');

        $response->assertStatus(200);
        expect($response->headers->get('Content-Type'))->toContain('text/xml');
    });

});

describe('OAI-PMH ListRecords — format oai_dc', function () {

    it('mengembalikan records dengan metadataPrefix=oai_dc', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=ListRecords&metadataPrefix=oai_dc'
        );

        $response->assertStatus(200);
        $content = $response->getContent();

        expect($content)->toContain('ListRecords');
        expect($content)->toContain('oai_dc');
    });

    it('mengembalikan XML yang well-formed untuk oai_dc', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=ListRecords&metadataPrefix=oai_dc'
        );

        $response->assertStatus(200);

        $dom    = new DOMDocument();
        $result = @$dom->loadXML($response->getContent());
        expect($result)->toBeTrue('Response harus berupa XML yang well-formed');
    });

});

describe('OAI-PMH ListRecords — format marc21', function () {

    it('mengembalikan records dengan metadataPrefix=marc21', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=ListRecords&metadataPrefix=marc21'
        );

        $response->assertStatus(200);
        $content = $response->getContent();

        expect($content)->toContain('ListRecords');
    });

    it('mengembalikan XML yang well-formed untuk marc21', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=ListRecords&metadataPrefix=marc21'
        );

        $response->assertStatus(200);

        $dom    = new DOMDocument();
        $result = @$dom->loadXML($response->getContent());
        expect($result)->toBeTrue('Response marc21 harus berupa XML yang well-formed');
    });

});

describe('OAI-PMH ListRecords — format rfc1807', function () {

    it('mengembalikan records dengan metadataPrefix=rfc1807', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=ListRecords&metadataPrefix=rfc1807'
        );

        $response->assertStatus(200);
        $content = $response->getContent();

        expect($content)->toContain('ListRecords');
    });

    it('mengembalikan XML yang well-formed untuk rfc1807', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=ListRecords&metadataPrefix=rfc1807'
        );

        $response->assertStatus(200);

        $dom    = new DOMDocument();
        $result = @$dom->loadXML($response->getContent());
        expect($result)->toBeTrue('Response rfc1807 harus berupa XML yang well-formed');
    });

});

describe('OAI-PMH GetRecord — multi-format', function () {

    it('mengembalikan GetRecord dengan metadataPrefix=oai_dc', function () {
        [$journal, $submission] = makeOaiJournal();

        $identifier = "oai:{$journal->slug}/{$submission->seq_id}";

        $response = $this->get(
            route('journal.oai', $journal->slug) .
            '?verb=GetRecord&metadataPrefix=oai_dc&identifier=' . urlencode($identifier)
        );

        $response->assertStatus(200);
        expect($response->getContent())->toContain('GetRecord');
    });

    it('mengembalikan GetRecord dengan metadataPrefix=marc21', function () {
        [$journal, $submission] = makeOaiJournal();

        $identifier = "oai:{$journal->slug}/{$submission->seq_id}";

        $response = $this->get(
            route('journal.oai', $journal->slug) .
            '?verb=GetRecord&metadataPrefix=marc21&identifier=' . urlencode($identifier)
        );

        $response->assertStatus(200);
        expect($response->getContent())->toContain('GetRecord');
    });

});

describe('OAI-PMH — Error Handling', function () {

    it('mengembalikan cannotDisseminateFormat untuk format yang tidak didukung', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=ListRecords&metadataPrefix=mods'
        );

        $response->assertStatus(200);
        expect($response->getContent())->toContain('cannotDisseminateFormat');
    });

    it('mengembalikan badVerb untuk verb yang tidak valid', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=InvalidVerb'
        );

        $response->assertStatus(200);
        expect($response->getContent())->toContain('badVerb');
    });

    it('mengembalikan badArgument jika metadataPrefix tidak ada untuk ListRecords', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=ListRecords'
        );

        $response->assertStatus(200);
        expect($response->getContent())->toContain('badArgument');
    });

    it('mengembalikan noRecordsMatch jika OAI tidak diaktifkan untuk journal', function () {
        $journal = Journal::factory()->create([
            'enabled'    => true,
            'enable_oai' => false,
        ]);

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=ListRecords&metadataPrefix=oai_dc'
        );

        $response->assertStatus(200);
        expect($response->getContent())->toContain('noRecordsMatch');
    });

    it('mengembalikan noRecordsMatch jika tidak ada artikel published', function () {
        $journal = Journal::factory()->create([
            'enabled'    => true,
            'enable_oai' => true,
        ]);

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=ListRecords&metadataPrefix=oai_dc'
        );

        $response->assertStatus(200);
        expect($response->getContent())->toContain('noRecordsMatch');
    });

    it('mengembalikan badArgument untuk parameter yang tidak diizinkan', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=Identify&invalidParam=test'
        );

        $response->assertStatus(200);
        expect($response->getContent())->toContain('badArgument');
    });

});

describe('OAI-PMH Identify', function () {

    it('mengembalikan Identify dengan informasi journal yang benar', function () {
        [$journal] = makeOaiJournal();

        $response = $this->get(
            route('journal.oai', $journal->slug) . '?verb=Identify'
        );

        $response->assertStatus(200);
        $content = $response->getContent();

        expect($content)->toContain('Identify');
        expect($content)->toContain($journal->name);
    });

});
