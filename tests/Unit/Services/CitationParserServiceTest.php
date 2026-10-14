<?php

namespace Tests\Unit\Services;

use App\Services\CitationParserService;
use PHPUnit\Framework\TestCase;

class CitationParserServiceTest extends TestCase
{
    public function test_parses_apa_reference_correctly(): void
    {
        $raw = "Evaluslana, E., & Wongso, C. (2023). Deep learning in medicine: A comprehensive review. Journal of Health AI, 3(2), 20-29. https://doi.org/10.1234/jhai.v3i2.1";
        $parsed = CitationParserService::parse($raw);

        $this->assertTrue($parsed['is_structured']);
        $this->assertEquals('10.1234/jhai.v3i2.1', $parsed['doi']);
        $this->assertEquals(2023, $parsed['year']);
        $this->assertEquals('Deep learning in medicine: A comprehensive review', $parsed['title']);
        $this->assertEquals('Journal of Health AI', $parsed['source']);
        $this->assertEquals('3', $parsed['volume']);
        $this->assertEquals('2', $parsed['issue']);
        $this->assertEquals('20', $parsed['first_page']);
        $this->assertEquals('29', $parsed['last_page']);
        $this->assertNotEmpty($parsed['authors']);
        $this->assertContains('Evaluslana, E.', $parsed['authors']);
    }

    public function test_parses_vancouver_reference_correctly(): void
    {
        $raw = "Evaluslana E, Wongso C. Clinical application of neural networks. J Med Inform. 2022;15(3):105-112. doi: 10.5678/jmi.2022.15";
        $parsed = CitationParserService::parse($raw);

        $this->assertTrue($parsed['is_structured']);
        $this->assertEquals('10.5678/jmi.2022.15', $parsed['doi']);
        $this->assertEquals(2022, $parsed['year']);
        $this->assertEquals('Clinical application of neural networks', $parsed['title']);
        $this->assertEquals('J Med Inform', $parsed['source']);
        $this->assertEquals('15', $parsed['volume']);
        $this->assertEquals('3', $parsed['issue']);
        $this->assertEquals('105', $parsed['first_page']);
        $this->assertEquals('112', $parsed['last_page']);
        $this->assertNotEmpty($parsed['authors']);
    }

    public function test_parses_ieee_reference_with_leading_brackets_and_quotes(): void
    {
        $raw = '[1] E. Evaluslana and C. Wongso, "Blockchain protocols for academic publishing," IEEE Trans. Knowledge Eng., vol. 12, no. 4, pp. 45-56, 2021.';
        $parsed = CitationParserService::parse($raw);

        $this->assertTrue($parsed['is_structured']);
        $this->assertEquals(2021, $parsed['year']);
        $this->assertEquals('Blockchain protocols for academic publishing', $parsed['title']);
        $this->assertEquals('12', $parsed['volume']);
        $this->assertEquals('4', $parsed['issue']);
        $this->assertEquals('45', $parsed['first_page']);
        $this->assertEquals('56', $parsed['last_page']);
        $this->assertNotEmpty($parsed['authors']);
        $this->assertContains('E. Evaluslana', $parsed['authors']);
        $this->assertContains('C. Wongso', $parsed['authors']);
    }

    public function test_normalizes_em_dashes_en_dashes_and_curly_quotes(): void
    {
        // Em-dash '—' in pages, curly quotes “ ” in title
        $raw = '[12] A. B. Smith, “Advances in Quantum Encryption,” Cybernetics Review, vol. 8, no. 1, pp. 100—115, 2020.';
        $parsed = CitationParserService::parse($raw);

        $this->assertTrue($parsed['is_structured']);
        $this->assertEquals('Advances in Quantum Encryption', $parsed['title']);
        $this->assertEquals('100', $parsed['first_page']);
        $this->assertEquals('115', $parsed['last_page']);
        $this->assertEquals(2020, $parsed['year']);
    }

    public function test_marks_unstructured_if_insufficient_fields(): void
    {
        $raw = "Random website citation without details http://example.com/some-page";
        $parsed = CitationParserService::parse($raw);

        $this->assertFalse($parsed['is_structured']);
        $this->assertNull($parsed['year']);
        $this->assertNull($parsed['title']);
    }

    public function test_parses_vancouver_with_author_initial_dots(): void
    {
        $raw = "Doe J., Smith B. Title of article. Journal Name. 2023;10(2):1-5.";
        $parsed = CitationParserService::parse($raw);

        $this->assertTrue($parsed['is_structured']);
        $this->assertEquals('Title of article', $parsed['title']);
        $this->assertEquals('Journal Name', $parsed['source']);
        $this->assertEquals(2023, $parsed['year']);
        $this->assertEquals('10', $parsed['volume']);
        $this->assertEquals('2', $parsed['issue']);
        $this->assertEquals('1', $parsed['first_page']);
        $this->assertEquals('5', $parsed['last_page']);
        $this->assertCount(2, $parsed['authors']);
        $this->assertEquals('Doe J.', $parsed['authors'][0]);
        $this->assertEquals('Smith B.', $parsed['authors'][1]);
    }

    public function test_parses_apa_with_three_or_more_authors(): void
    {
        $raw = "Smith, J. A., Wong, B. C., & Doe, E. F. (2023). Great Title. Great Journal, 1(1), 1-10.";
        $parsed = CitationParserService::parse($raw);

        $this->assertTrue($parsed['is_structured']);
        $this->assertEquals('Great Title', $parsed['title']);
        $this->assertEquals('Great Journal', $parsed['source']);
        $this->assertEquals(2023, $parsed['year']);
        $this->assertCount(3, $parsed['authors']);
        $this->assertEquals('Smith, J. A.', $parsed['authors'][0]);
        $this->assertEquals('Wong, B. C.', $parsed['authors'][1]);
        $this->assertEquals('Doe, E. F.', $parsed['authors'][2]);
    }

    public function test_parses_apa_with_question_mark_title(): void
    {
        $raw = "Smith, J. (2023). Can Artificial Intelligence Replace Doctors? Medical AI Journal, 5(1), 10-20.";
        $parsed = CitationParserService::parse($raw);

        $this->assertTrue($parsed['is_structured']);
        $this->assertEquals('Can Artificial Intelligence Replace Doctors', $parsed['title']);
        $this->assertEquals('Medical AI Journal', $parsed['source']);
        $this->assertEquals(2023, $parsed['year']);
        $this->assertEquals('5', $parsed['volume']);
        $this->assertEquals('1', $parsed['issue']);
    }

    public function test_parses_ieee_with_single_quotes(): void
    {
        $raw = "[1] A. B. Smith, 'Testing Single Quote Titles', Tech Rev, 2023.";
        $parsed = CitationParserService::parse($raw);

        $this->assertTrue($parsed['is_structured']);
        $this->assertEquals('Testing Single Quote Titles', $parsed['title']);
        $this->assertEquals('Tech Rev', $parsed['source']);
        $this->assertEquals(2023, $parsed['year']);
        $this->assertContains('A. B. Smith', $parsed['authors']);
    }

    public function test_parses_ieee_with_three_authors(): void
    {
        $raw = '[1] A. B. Smith, C. D. Wong, and E. F. Doe, "Testing Titles", Tech Rev, 2023.';
        $parsed = CitationParserService::parse($raw);

        $this->assertTrue($parsed['is_structured']);
        $this->assertCount(3, $parsed['authors']);
        $this->assertEquals('A. B. Smith', $parsed['authors'][0]);
        $this->assertEquals('C. D. Wong', $parsed['authors'][1]);
        $this->assertEquals('E. F. Doe', $parsed['authors'][2]);
    }

    public function test_parse_all_splits_multiline_references(): void
    {
        $blob = "Evaluslana, E. (2023). Article One. Journal of Tests, 1(1), 1-10.\n\n[2] Wongso, C., \"Article Two,\" Tech Journal, vol. 2, no. 1, pp. 20-30, 2022.";
        $results = CitationParserService::parseAll($blob);

        $this->assertCount(2, $results);
        $this->assertEquals(1, $results[0]['seq']);
        $this->assertEquals(2, $results[1]['seq']);
        $this->assertEquals('Article One', $results[0]['title']);
        $this->assertEquals('Article Two', $results[1]['title']);
    }
}
