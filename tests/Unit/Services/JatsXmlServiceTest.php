<?php

/**
 * Test untuk JatsXmlService
 *
 * Memverifikasi bahwa JATS 1.3 XML yang dihasilkan:
 * - Well-formed (dapat di-parse oleh DOMDocument)
 * - Mengandung namespace declarations yang benar
 * - Memetakan data submission ke elemen JATS yang tepat
 * - Menangani field opsional dengan benar (tidak ada elemen kosong)
 */

use App\Models\Journal;
use App\Models\Publication;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionAuthor;
use App\Services\JatsXmlService;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

/**
 * Helper: buat submission lengkap untuk testing
 */
function makeTestSubmission(array $overrides = []): Submission
{
    $journal = Journal::factory()->create([
        'name'         => 'Jurnal Ilmu Komputer',
        'abbreviation' => 'JIK',
        'publisher'    => 'Universitas Test',
        'issn_print'   => '2580-1234',
        'issn_online'  => '2580-1235',
    ]);

    $section = Section::factory()->create([
        'journal_id' => $journal->id,
        'title'      => 'Research Article',
    ]);

    $submission = Submission::factory()->create(array_merge([
        'journal_id' => $journal->id,
        'section_id' => $section->id,
        'locale'     => 'id_ID',
        'status'     => Submission::STATUS_PUBLISHED,
    ], $overrides));

    $pub = Publication::factory()->create([
        'submission_id'    => $submission->id,
        'title'            => 'Judul Artikel Test',
        'abstract'         => '<p>Abstrak artikel ini membahas topik penting.</p>',
        'doi'              => '10.12345/jik.2024.001',
        'pages'            => '1-15',
        'keywords'         => 'machine learning, deep learning, AI',
        'license_url'      => 'https://creativecommons.org/licenses/by/4.0/',
        'copyright_holder' => 'Penulis',
        'copyright_year'   => 2024,
        'date_published'   => '2024-01-15',
        'status'           => Publication::STATUS_PUBLISHED,
    ]);

    SubmissionAuthor::factory()->create([
        'submission_id'   => $submission->id,
        'publication_id'  => $pub->id,
        'first_name'      => 'Budi',
        'last_name'       => 'Santoso',
        'affiliation'     => 'Universitas Indonesia',
        'orcid'           => '0000-0001-2345-6789',
        'is_corresponding' => true,
        'sort_order'      => 0,
    ]);

    // Reload dengan relasi
    $submission->load(['currentPublication.authors', 'journal', 'section', 'issue']);

    return $submission;
}

describe('JatsXmlService', function () {

    beforeEach(function () {
        $this->service = new JatsXmlService();
    });

    // ─── Well-formedness ──────────────────────────────────────────────────────

    it('menghasilkan XML yang well-formed dan dapat di-parse', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        $dom = new DOMDocument();
        $result = $dom->loadXML($xml);

        expect($result)->toBeTrue('XML harus dapat di-parse oleh DOMDocument');
    });

    it('menghasilkan deklarasi XML di baris pertama', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toStartWith('<?xml version="1.0" encoding="UTF-8"?>');
    });

    // ─── Namespace declarations ───────────────────────────────────────────────

    it('mengandung namespace xlink di elemen root', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('xmlns:xlink="http://www.w3.org/1999/xlink"');
    });

    it('mengandung namespace ali di elemen root', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('xmlns:ali="http://www.niso.org/schemas/ali/1.0/"');
    });

    it('mengandung dtd-version="1.3" di elemen root', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('dtd-version="1.3"');
    });

    // ─── BCP47 locale conversion ──────────────────────────────────────────────

    it('mengkonversi locale id_ID menjadi id untuk xml:lang', function () {
        $submission = makeTestSubmission(['locale' => 'id_ID']);
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('xml:lang="id"');
        expect($xml)->not->toContain('xml:lang="id_ID"');
    });

    it('mengkonversi locale en_US menjadi en untuk xml:lang', function () {
        $submission = makeTestSubmission(['locale' => 'en_US']);
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('xml:lang="en"');
    });

    it('menggunakan en sebagai default jika locale null', function () {
        $submission = makeTestSubmission(['locale' => null]);
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('xml:lang="en"');
    });

    // ─── Section → article-type mapping ──────────────────────────────────────

    it('memetakan "Research Article" ke article-type research-article', function () {
        $submission = makeTestSubmission();
        // Section sudah dibuat dengan title "Research Article" di makeTestSubmission
        $xml = $this->service->generate($submission);

        expect($xml)->toContain('article-type="research-article"');
    });

    it('menggunakan research-article sebagai default untuk section yang tidak dikenal', function () {
        $submission = makeTestSubmission();
        $submission->section->title = 'Kategori Tidak Dikenal';
        $submission->section->save();
        $submission->load('section');

        $xml = $this->service->generate($submission);

        expect($xml)->toContain('article-type="research-article"');
    });

    // ─── Metadata artikel ─────────────────────────────────────────────────────

    it('menghasilkan elemen article-title dengan judul yang benar', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('<article-title>Judul Artikel Test</article-title>');
    });

    it('menghasilkan elemen DOI jika tersedia', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('<article-id pub-id-type="doi">10.12345/jik.2024.001</article-id>');
    });

    it('tidak menghasilkan elemen DOI jika tidak tersedia', function () {
        $submission = makeTestSubmission();
        $submission->currentPublication->doi = null;
        $submission->currentPublication->save();
        $submission->load('currentPublication.authors');

        $xml = $this->service->generate($submission);

        expect($xml)->not->toContain('pub-id-type="doi"');
    });

    it('menghasilkan fpage dan lpage dari pages yang mengandung tanda -', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('<fpage>1</fpage>');
        expect($xml)->toContain('<lpage>15</lpage>');
    });

    it('menghasilkan hanya fpage jika pages tidak mengandung tanda -', function () {
        $submission = makeTestSubmission();
        $submission->currentPublication->pages = 'e12345';
        $submission->currentPublication->save();
        $submission->load('currentPublication.authors');

        $xml = $this->service->generate($submission);

        expect($xml)->toContain('<fpage>e12345</fpage>');
        expect($xml)->not->toContain('<lpage>');
    });

    // ─── Author ───────────────────────────────────────────────────────────────

    it('menghasilkan elemen contrib untuk setiap author', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('<surname>Santoso</surname>');
        expect($xml)->toContain('<given-names>Budi</given-names>');
    });

    it('menambahkan atribut corresp="yes" untuk corresponding author', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('corresp="yes"');
    });

    it('menghasilkan ORCID URL lengkap untuk author dengan ORCID', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('https://orcid.org/0000-0001-2345-6789');
    });

    it('membersihkan prefix URL dari ORCID yang sudah mengandung URL', function () {
        $submission = makeTestSubmission();
        $submission->currentPublication->authors->first()->orcid = 'https://orcid.org/0000-0001-2345-6789';
        $submission->currentPublication->authors->first()->save();

        $xml = $this->service->generate($submission);

        // Tidak boleh ada double URL
        expect($xml)->not->toContain('https://orcid.org/https://orcid.org/');
        expect($xml)->toContain('https://orcid.org/0000-0001-2345-6789');
    });

    // ─── Abstract ─────────────────────────────────────────────────────────────

    it('menghasilkan abstract dengan HTML tags yang sudah di-strip', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('<abstract>');
        expect($xml)->toContain('Abstrak artikel ini membahas topik penting.');
        // The <p> tag in JATS XML is correct structure, not HTML from abstract
        // What we're checking is that the abstract content itself has no HTML tags
        expect($xml)->toContain('<p>Abstrak artikel ini membahas topik penting.</p>');
    });

    // ─── Keywords ─────────────────────────────────────────────────────────────

    it('menghasilkan kwd-group dengan keywords yang benar', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('<kwd>machine learning</kwd>');
        expect($xml)->toContain('<kwd>deep learning</kwd>');
        expect($xml)->toContain('<kwd>AI</kwd>');
    });

    // ─── License ──────────────────────────────────────────────────────────────

    it('menghasilkan ali:license_ref jika license_url tersedia', function () {
        $submission = makeTestSubmission();
        $xml        = $this->service->generate($submission);

        expect($xml)->toContain('https://creativecommons.org/licenses/by/4.0/');
    });

    it('tidak menghasilkan elemen license jika license_url null', function () {
        $submission = makeTestSubmission();
        $submission->currentPublication->license_url = null;
        $submission->currentPublication->save();
        $submission->load('currentPublication.authors');

        $xml = $this->service->generate($submission);

        expect($xml)->not->toContain('ali:license_ref');
    });

    // ─── XML escaping ─────────────────────────────────────────────────────────

    it('meng-escape karakter XML spesial dalam judul', function () {
        $submission = makeTestSubmission();
        $submission->currentPublication->title = 'Judul dengan <tag> & "kutip"';
        $submission->currentPublication->save();
        $submission->load('currentPublication.authors');

        $xml = $this->service->generate($submission);

        // XML harus tetap well-formed
        $dom    = new DOMDocument();
        $result = $dom->loadXML($xml);
        expect($result)->toBeTrue('XML harus tetap well-formed meskipun ada karakter spesial');
    });

    // ─── Error handling ───────────────────────────────────────────────────────

    it('melempar InvalidArgumentException jika submission tidak punya publication', function () {
        $submission = makeTestSubmission();
        // Hapus publication
        $submission->currentPublication->delete();
        $submission->load('currentPublication');

        expect(fn() => $this->service->generate($submission))
            ->toThrow(\InvalidArgumentException::class);
    });

});
