<?php

namespace Tests\Unit\Services;

use App\Models\Citation;
use App\Services\CitationService;
use PHPUnit\Framework\TestCase;

class CitationServiceTest extends TestCase
{
    public function test_scholar_meta_emits_key_value_pairs_when_title_and_year_exist(): void
    {
        $raw = "Evaluslana, E. (2023). Deep learning in medicine. Journal of Health AI, 3(2), 20-29. https://doi.org/10.1234/jhai.v3i2.1";
        $meta = CitationService::parseReferenceToScholarMeta($raw);

        $this->assertStringContainsString('citation_doi=10.1234/jhai.v3i2.1;', $meta);
        $this->assertStringContainsString('citation_title=Deep learning in medicine;', $meta);
        $this->assertStringContainsString('citation_publication_date=2023;', $meta);
        $this->assertStringContainsString('citation_author=Evaluslana, E.;', $meta);
        $this->assertStringContainsString('citation_journal_title=Journal of Health AI;', $meta);
        $this->assertStringContainsString('citation_volume=3;', $meta);
        $this->assertStringContainsString('citation_issue=2;', $meta);
        $this->assertStringContainsString('citation_firstpage=20;', $meta);
        $this->assertStringContainsString('citation_lastpage=29;', $meta);
    }

    public function test_scholar_meta_falls_back_to_raw_string_when_title_missing(): void
    {
        // Missing title — only volume and pages
        $raw = "Some Broken Reference, Vol. 3 No. 2, pp. 20-29";
        $meta = CitationService::parseReferenceToScholarMeta($raw);

        // Must NOT emit broken tags like "citation_volume=3; citation_firstpage=20;"
        $this->assertEquals($raw, $meta);
    }

    public function test_scholar_meta_falls_back_to_raw_string_when_year_missing(): void
    {
        // Has a quoted title but no year
        $raw = 'E. Evaluslana, "Neural Networks in Diagnosis", Journal of Med';
        $meta = CitationService::parseReferenceToScholarMeta($raw);

        // Must return raw string since year is missing
        $this->assertEquals($raw, $meta);
    }

    public function test_scholar_meta_handles_ieee_style_with_brackets(): void
    {
        $raw = '[1] E. Evaluslana, "Cloud Computing Architecture," IEEE Trans. Services Computing, vol. 5, no. 1, pp. 10-20, 2021.';
        $meta = CitationService::parseReferenceToScholarMeta($raw);

        $this->assertStringContainsString('citation_title=Cloud Computing Architecture;', $meta);
        $this->assertStringContainsString('citation_publication_date=2021;', $meta);
        $this->assertStringContainsString('citation_author=E. Evaluslana;', $meta);
        $this->assertStringContainsString('citation_volume=5;', $meta);
    }

    public function test_citation_model_to_scholar_meta_content_enforces_title_author_year(): void
    {
        // 1. All required fields present
        $citation = new Citation([
            'raw_citation' => 'Evaluslana, E. (2023). Test Title.',
            'title'        => 'Test Title',
            'authors'      => ['Evaluslana, E.'],
            'year'         => 2023,
            'source'       => 'Journal of Tests',
        ]);
        $meta = $citation->toScholarMetaContent();
        $this->assertStringContainsString('citation_title=Test Title;', $meta);
        $this->assertStringContainsString('citation_author=Evaluslana, E.;', $meta);
        $this->assertStringContainsString('citation_publication_date=2023;', $meta);
        $this->assertStringContainsString('citation_journal_title=Journal of Tests;', $meta);

        // 2. Missing author -> falls back to raw_citation
        $citationNoAuthor = new Citation([
            'raw_citation' => 'Anonymous (2023). Test Title Without Author.',
            'title'        => 'Test Title Without Author',
            'authors'      => null,
            'year'         => 2023,
        ]);
        $this->assertEquals('Anonymous (2023). Test Title Without Author.', $citationNoAuthor->toScholarMetaContent());

        // 3. Missing title -> falls back to raw_citation
        $citationNoTitle = new Citation([
            'raw_citation' => 'Evaluslana, E. (2023).',
            'title'        => null,
            'authors'      => ['Evaluslana, E.'],
            'year'         => 2023,
        ]);
        $this->assertEquals('Evaluslana, E. (2023).', $citationNoTitle->toScholarMetaContent());
    }
}
