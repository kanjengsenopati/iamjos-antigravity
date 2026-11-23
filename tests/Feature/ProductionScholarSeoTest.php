<?php

namespace Tests\Feature;

use App\Models\Issue;
use App\Models\Journal;
use App\Models\Publication;
use App\Models\PublicationGalley;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionAuthor;
use App\Models\SubmissionFile;
use App\Models\User;
use App\Services\CitationService;
use App\Services\GoogleScholarValidator;
use DOMDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionScholarSeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_serves_dynamic_robots_txt_with_active_host_sitemap_url(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $this->assertStringContainsString('text/plain', (string) $response->headers->get('Content-Type'));
        $body = $response->getContent();
        $this->assertStringContainsString('User-agent: *', $body);
        $this->assertStringContainsString('Sitemap: ' . url('/sitemap.xml'), $body);
        $this->assertStringNotContainsString('apps.iamjos.id', $body);
    }

    public function test_emits_single_canonical_single_og_url_strict_pdf_url_and_no_fake_fulltext_html_url(): void
    {
        $journal = Journal::factory()->create([
            'enabled' => true,
            'slug' => 'joallp-test',
            'name' => 'Journal of Applied Linguistics',
        ]);
        $section = Section::factory()->create(['journal_id' => $journal->id, 'title' => 'Research Article']);
        $issue = Issue::factory()->create([
            'journal_id' => $journal->id,
            'seq_id' => 11,
            'volume' => '2',
            'number' => '1',
            'year' => 2026,
            'show_volume' => true,
            'show_number' => true,
            'is_published' => true,
            'published_at' => now(),
        ]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'section_id' => $section->id,
            'issue_id' => $issue->id,
            'title' => "Calum's Defense Mechanisms <script>alert(1)</script>",
            'abstract' => '<p>Abstract with &amp; entity.</p>',
            'status' => Submission::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        $pub = Publication::factory()->create([
            'submission_id' => $submission->id,
            'section_id' => $section->id,
            'issue_id' => $issue->id,
            'title' => "Calum's Defense Mechanisms <script>alert(1)</script>",
            'abstract' => '<p>Abstract with &amp; entity.</p>',
            'date_published' => '2026-09-30',
            'status' => Publication::STATUS_PUBLISHED,
        ]);
        SubmissionAuthor::factory()->create([
            'submission_id' => $submission->id,
            'publication_id' => $pub->id,
            'first_name' => 'Nadia',
            'last_name' => 'Tyas',
        ]);

        $user = User::factory()->create();
        $pdfFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'uploaded_by' => $user->id,
            'file_name' => 'paper.pdf',
            'file_path' => 'journals/test/paper.pdf',
            'file_size' => 2048,
            'mime_type' => 'application/pdf',
            'file_type' => 'galley',
            'stage' => SubmissionFile::STAGE_PRODUCTION,
        ]);
        PublicationGalley::create([
            'submission_id' => $submission->id,
            'file_id' => $pdfFile->id,
            'label' => 'PDF',
            'locale' => 'en',
            'is_remote' => false,
            'seq' => 0,
        ]);

        $response = $this->get(route('journal.public.article', [
            'journal' => $journal->slug,
            'article' => $submission->seq_id,
        ]));

        $response->assertStatus(200);
        $html = $response->getContent();

        $this->assertSame(1, substr_count($html, 'rel="canonical"'));
        $this->assertSame(1, substr_count($html, 'property="og:url"'));
        $this->assertStringContainsString('<meta name="citation_pdf_url"', $html);
        $this->assertStringContainsString('.pdf"', $html);
        $this->assertStringNotContainsString('<meta name="citation_fulltext_html_url"', $html);
        $this->assertStringNotContainsString("document.querySelector('[x-html]')", $html);
        $this->assertStringContainsString('x-ref="citationOutput"', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_avoids_double_xml_escaping_in_oai_dc_records(): void
    {
        $journal = Journal::factory()->create([
            'enabled' => true,
            'enable_oai' => true,
            'name' => 'Applied Linguistics & Practice',
        ]);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'title' => "Calum's Defense & Anna Freud's Theory",
            'status' => Submission::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        $pub = Publication::factory()->create([
            'submission_id' => $submission->id,
            'title' => "Calum's Defense & Anna Freud's Theory",
            'status' => Publication::STATUS_PUBLISHED,
        ]);
        SubmissionAuthor::factory()->create([
            'submission_id' => $submission->id,
            'publication_id' => $pub->id,
            'first_name' => 'Nadia',
            'last_name' => 'Tyas',
        ]);

        $response = $this->get(route('journal.oai', $journal->slug) . '?verb=ListRecords&metadataPrefix=oai_dc');
        $response->assertStatus(200);
        $xml = $response->getContent();

        $this->assertStringNotContainsString('&amp;apos;', $xml);
        $this->assertStringNotContainsString('&amp;amp;', $xml);
        $this->assertStringContainsString('Calum&apos;s Defense &amp; Anna Freud&apos;s Theory', $xml);

        $dom = new DOMDocument();
        $this->assertTrue($dom->loadXML($xml));
    }

    public function test_sanitizes_ris_bibtex_citation_service_xss_and_validator_blank_lines(): void
    {
        $journal = Journal::factory()->create(['enabled' => true, 'name' => 'Journal <script>']);
        $submission = Submission::factory()->create([
            'journal_id' => $journal->id,
            'title' => "Title\r\nTY  - BOOK {Braces} \\ % & _ <x>",
            'status' => Submission::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        $pub = Publication::factory()->create([
            'submission_id' => $submission->id,
            'title' => "Title\r\nTY  - BOOK {Braces} \\ % & _ <x>",
            'doi' => '10.1234/a_b#c',
            'references' => "\n\n\n\n\n\n\n\n\n\n\n\n",
            'keywords' => json_encode(['one', 'two', 'three']),
            'status' => Publication::STATUS_PUBLISHED,
        ]);
        SubmissionAuthor::factory()->create([
            'submission_id' => $submission->id,
            'publication_id' => $pub->id,
            'first_name' => '<b>Alice</b>',
            'last_name' => 'Smith',
        ]);

        $submission->load(['journal', 'issue', 'currentPublication.authors', 'authors']);
        $formats = (new CitationService())->getAllFormats($submission);
        foreach ($formats as $html) {
            $this->assertStringNotContainsString('<script>', $html);
            $this->assertStringNotContainsString('<b>', $html);
        }

        $risResp = $this->get(route('citation.ris', ['journal' => $journal->slug, 'article' => $submission->seq_id]));
        $risResp->assertStatus(200);
        $this->assertStringNotContainsString("\nTY  - BOOK", $risResp->getContent());

        $bibResp = $this->get(route('citation.bibtex', ['journal' => $journal->slug, 'article' => $submission->seq_id]));
        $bibResp->assertStatus(200);
        $bibBody = $bibResp->getContent();
        $this->assertStringContainsString('\\{Braces\\}', $bibBody);
        $this->assertStringContainsString('\\textbackslash{}', $bibBody);
        $this->assertStringContainsString('10.1234/a\\_b\\#c', $bibBody);

        $validation = (new GoogleScholarValidator())->validate($submission, $pub);
        $refCheck = collect($validation['checks'])->firstWhere('label', GoogleScholarValidator::LABEL_REFERENCES);
        $kwCheck = collect($validation['checks'])->firstWhere('label', 'Keywords');
        $this->assertSame(0, $refCheck['score']);
        $this->assertSame(10, $kwCheck['score']);
    }
}
