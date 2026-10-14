<?php

namespace Tests\Feature;

use App\Jobs\ProcessCitationsJob;
use App\Models\Citation;
use App\Models\Issue;
use App\Models\Journal;
use App\Models\Publication;
use App\Models\Section;
use App\Models\Submission;
use App\Models\SubmissionAuthor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CitationLinkingTest extends TestCase
{
    use DatabaseTransactions;

    protected Journal $journalA;
    protected Journal $journalB;
    protected Section $sectionA;
    protected Section $sectionB;
    protected User $user;
    protected Issue $issueA;
    protected Issue $issueB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->journalA = Journal::factory()->create([
            'name'       => 'Journal of Computer Science',
            'slug'       => 'jcs-' . uniqid(),
            'doi_prefix' => '10.1234',
            'enabled'    => true,
        ]);
        $this->sectionA = Section::factory()->create(['journal_id' => $this->journalA->id]);
        $this->issueA = Issue::create([
            'journal_id'   => $this->journalA->id,
            'volume'       => 1,
            'number'       => 1,
            'year'         => 2023,
            'seq_id'       => 1,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->journalB = Journal::factory()->create([
            'name'       => 'Journal of Applied Informatics',
            'slug'       => 'jai-' . uniqid(),
            'doi_prefix' => '10.5678',
            'enabled'    => true,
        ]);
        $this->sectionB = Section::factory()->create(['journal_id' => $this->journalB->id]);
        $this->issueB = Issue::create([
            'journal_id'   => $this->journalB->id,
            'volume'       => 2,
            'number'       => 1,
            'year'         => 2024,
            'seq_id'       => 2,
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    protected function createPublishedArticle(Journal $journal, Section $section, Issue $issue, string $title, ?string $doi, int $year, array $authors): array
    {
        $submission = Submission::create([
            'journal_id'   => $journal->id,
            'section_id'   => $section->id,
            'user_id'      => $this->user->id,
            'issue_id'     => $issue->id,
            'title'        => $title,
            'locale'       => 'en',
            'status'       => Submission::STATUS_PUBLISHED,
            'stage'        => Submission::STAGE_PRODUCTION,
            'submitted_at' => now()->subMonths(6),
            'published_at' => now()->subMonths(3),
            'seq_id'       => random_int(10000, 999999),
        ]);

        $publication = Publication::create([
            'submission_id'  => $submission->id,
            'section_id'     => $section->id,
            'issue_id'       => $issue->id,
            'version'        => 1,
            'status'         => Publication::STATUS_PUBLISHED,
            'title'          => $title,
            'doi'            => $doi,
            'date_published' => "{$year}-01-15",
            'copyright_year' => $year,
        ]);

        foreach ($authors as $i => $authorData) {
            SubmissionAuthor::create([
                'submission_id'  => $submission->id,
                'publication_id' => $publication->id,
                'given_name'     => $authorData['first_name'] ?? 'John',
                'family_name'    => $authorData['last_name'] ?? 'Doe',
                'first_name'     => $authorData['first_name'] ?? 'John',
                'last_name'      => $authorData['last_name'] ?? 'Doe',
                'name'           => ($authorData['first_name'] ?? 'John') . ' ' . ($authorData['last_name'] ?? 'Doe'),
                'email'          => 'author' . uniqid() . '@example.com',
                'sort_order'     => $i + 1,
            ]);
        }

        return [$submission, $publication];
    }

    public function test_cross_journal_citation_linking_between_two_journals(): void
    {
        // 1. Create 5 published articles in Journal A
        [$subA1, $pubA1] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Neural Network Architectures for Medical Imaging',
            '10.1234/jcs.v1i1.1',
            2023,
            [['first_name' => 'Alice', 'last_name' => 'Wong']]
        );

        [$subA2, $pubA2] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Distributed Consensus in Large Scale Blockchain Networks',
            '10.1234/jcs.v1i1.2',
            2023,
            [['first_name' => 'Bob', 'last_name' => 'Smith']]
        );

        [$subA3, $pubA3] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Evaluating Quantum Key Distribution Protocols',
            '10.1234/jcs.v1i1.3',
            2023,
            [['first_name' => 'Charlie', 'last_name' => 'Davis']]
        );

        [$subA4, $pubA4] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Autonomous Navigation Systems for Micro Drones',
            '10.1234/jcs.v1i1.4',
            2023,
            [['first_name' => 'David', 'last_name' => 'Taylor']]
        );

        [$subA5, $pubA5] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Privacy Preserving Federated Learning in Smart Healthcare',
            '10.1234/jcs.v1i1.5',
            2023,
            [['first_name' => 'Elena', 'last_name' => 'Evaluslana']]
        );

        // Verify initial citation counts are 0
        $this->assertEquals(0, $subA1->fresh()->citation_count);
        $this->assertEquals(0, $subA2->fresh()->citation_count);
        $this->assertEquals(0, $subA3->fresh()->citation_count);
        $this->assertEquals(0, $subA4->fresh()->citation_count);
        $this->assertEquals(0, $subA5->fresh()->citation_count);

        // 2. Create Article B1 in Journal B that references all 5 articles from Journal A
        // using different bibliographic formats
        $references = implode("\n", [
            // Tier 1 (DOI match):
            "Wong, A. (2023). Neural Network Architectures for Medical Imaging. Journal of Computer Science, 1(1), 10-20. https://doi.org/10.1234/jcs.v1i1.1",
            // Tier 2 (Author surname & Year match):
            "Smith, B. (2023). Distributed Consensus in Large Scale Blockchain Networks. Journal of Computer Science, 1(1), 21-30.",
            // Vancouver style:
            "Davis C. Evaluating Quantum Key Distribution Protocols. J Comp Sci. 2023;1(1):31-40. https://doi.org/10.1234/jcs.v1i1.3",
            // IEEE style:
            "[4] D. Taylor, \"Autonomous Navigation Systems for Micro Drones,\" Journal of Computer Science, vol. 1, no. 1, pp. 41-50, 2023.",
            // Tier 3 (Normalized Title match):
            "Evaluslana, E. (2023). Privacy Preserving Federated Learning in Smart Healthcare. Journal of Computer Science.",
            // External reference (not on platform):
            "Einstein, A. (1905). Zur Elektrodynamik bewegter Körper. Annalen der Physik, 17, 891-921.",
            // Unstructured reference:
            "World Health Organization Technical Report 2022 on Global Pandemic Preparedness",
        ]);

        [$subB1, $pubB1] = $this->createPublishedArticle(
            $this->journalB, $this->sectionB, $this->issueB,
            'Comprehensive Survey on Modern Computing Paradigms',
            '10.5678/jai.v2i1.1',
            2024,
            [['first_name' => 'Frank', 'last_name' => 'Miller']]
        );

        $pubB1->update(['references' => $references]);

        // 3. Process Citations
        ProcessCitationsJob::dispatchSync($pubB1->id);

        // 4. Assert all 7 citations were created
        $citations = Citation::where('publication_id', $pubB1->id)->orderBy('seq')->get();
        $this->assertCount(7, $citations);

        // Assert 5 articles from Journal A are properly linked!
        $this->assertEquals($subA1->id, $citations[0]->target_submission_id, 'Ref 1 matched by DOI');
        $this->assertEquals('doi', $citations[0]->matched_by);

        $this->assertEquals($subA2->id, $citations[1]->target_submission_id, 'Ref 2 matched by Author/Year/Title');

        $this->assertEquals($subA3->id, $citations[2]->target_submission_id, 'Ref 3 matched by Vancouver DOI');

        $this->assertEquals($subA4->id, $citations[3]->target_submission_id, 'Ref 4 matched by IEEE title');

        $this->assertEquals($subA5->id, $citations[4]->target_submission_id, 'Ref 5 matched by Title Normalized');

        // External & unstructured references should have null target_submission_id
        $this->assertNull($citations[5]->target_submission_id, 'External reference has no internal target');
        $this->assertNull($citations[6]->target_submission_id, 'Unstructured reference has no internal target');

        // 5. Assert citation_count on Journal A articles updated to 1!
        $this->assertEquals(1, $subA1->fresh()->citation_count);
        $this->assertEquals(1, $subA2->fresh()->citation_count);
        $this->assertEquals(1, $subA3->fresh()->citation_count);
        $this->assertEquals(1, $subA4->fresh()->citation_count);
        $this->assertEquals(1, $subA5->fresh()->citation_count);

        // 6. Assert getCitingSubmissions() on SubA1 returns SubB1
        $citingSubmissions = $subA1->getCitingSubmissions();
        $this->assertCount(1, $citingSubmissions);
        $this->assertEquals($subB1->id, $citingSubmissions->first()->id);
        $this->assertEquals($this->journalB->name, $citingSubmissions->first()->journal->name);

        // 7. Verify PKP OJS Scholar Meta Rendering: 100% of references are rendered
        // Structured references output key-value; unstructured outputs raw string
        foreach ($citations as $cit) {
            $metaContent = $cit->toScholarMetaContent();
            $this->assertNotEmpty($metaContent);

            if ($cit->is_structured && $cit->title && $cit->authors && $cit->year) {
                $this->assertStringContainsString('citation_title=', $metaContent);
                $this->assertStringContainsString('citation_author=', $metaContent);
                $this->assertStringContainsString('citation_publication_date=', $metaContent);
            }
        }

        // The unstructured reference (#7) must output the raw string (no broken tags)
        $this->assertEquals(
            'World Health Organization Technical Report 2022 on Global Pandemic Preparedness',
            $citations[6]->toScholarMetaContent()
        );
    }

    public function test_issue_publishing_lifecycle_updates_publication_status(): void
    {
        // 1. Create unpublished issue
        $issue = Issue::create([
            'journal_id'   => $this->journalA->id,
            'volume'       => 2,
            'number'       => 1,
            'year'         => 2024,
            'seq_id'       => 3,
            'is_published' => false,
            'published_at' => null,
        ]);

        // 2. Create accepted submission with scheduled publication
        $sub = Submission::create([
            'journal_id'   => $this->journalA->id,
            'section_id'   => $this->sectionA->id,
            'user_id'      => $this->user->id,
            'issue_id'     => $issue->id,
            'title'        => 'Lifecycle Test Article',
            'status'       => Submission::STATUS_ACCEPTED,
            'stage'        => Submission::STAGE_PRODUCTION,
            'seq_id'       => random_int(10000, 999999),
        ]);

        $pub = Publication::create([
            'submission_id'  => $sub->id,
            'section_id'     => $this->sectionA->id,
            'issue_id'       => $issue->id,
            'version'        => 1,
            'status'         => Publication::STATUS_SCHEDULED,
            'title'          => 'Lifecycle Test Article',
            'references'     => "Author, A. (2020). Sample Title. Sample Journal, 1(1), 1-10.",
            'date_published' => null,
        ]);

        $this->assertEquals(Publication::STATUS_SCHEDULED, $pub->fresh()->status);
        $this->assertEquals(Submission::STATUS_ACCEPTED, $sub->fresh()->status);

        // 3. Publish issue via controller logic
        app()->instance('currentJournal', $this->journalA);
        $controller = new \App\Http\Controllers\Journal\IssueController();
        $request = new \Illuminate\Http\Request();
        $controller->publish($request, $issue);

        // Verify issue, submission, and publication are published!
        $this->assertTrue($issue->fresh()->is_published);
        $this->assertEquals(Submission::STATUS_PUBLISHED, $sub->fresh()->status);
        $this->assertEquals(Publication::STATUS_PUBLISHED, $pub->fresh()->status);
        $this->assertNotNull($pub->fresh()->date_published);

        // Verify citations were created via PublicationObserver -> ProcessCitationsJob
        $this->assertDatabaseHas('citations', [
            'publication_id' => $pub->id,
            'title'          => 'Sample Title',
        ]);

        // 4. Unpublish issue
        $controller->unpublish($issue);

        $this->assertFalse($issue->fresh()->is_published);
        $this->assertEquals(Submission::STATUS_ACCEPTED, $sub->fresh()->status);
        $this->assertEquals(Publication::STATUS_UNPUBLISHED, $pub->fresh()->status);
    }

    public function test_citations_process_artisan_command_backfills_and_recalculates(): void
    {
        // 1. Create a published article in Journal A
        [$subA, $pubA] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Original Groundbreaking Research on AI Systems',
            '10.1234/jcs.orig.1',
            2023,
            [['first_name' => 'Grace', 'last_name' => 'Hopper']]
        );

        // 2. Create citing article in Journal B
        [$subB, $pubB] = $this->createPublishedArticle(
            $this->journalB, $this->sectionB, $this->issueB,
            'Follow Up Study on AI Architectures',
            '10.5678/jai.followup.1',
            2024,
            [['first_name' => 'Alan', 'last_name' => 'Turing']]
        );

        $pubB->update([
            'references' => "Hopper, G. (2023). Original Groundbreaking Research on AI Systems. Journal of Computer Science, 1(1), 1-10. https://doi.org/10.1234/jcs.orig.1"
        ]);

        // Clear any existing citations to simulate an un-backfilled state
        Citation::where('publication_id', $pubB->id)->delete();
        $subA->update(['citation_count' => 0]);

        $this->assertEquals(0, Citation::where('publication_id', $pubB->id)->count());
        $this->assertEquals(0, $subA->fresh()->citation_count);

        // 3. Run php artisan citations:process --sync --force
        $exitCode = \Illuminate\Support\Facades\Artisan::call('citations:process', [
            '--sync'  => true,
            '--force' => true,
        ]);

        $this->assertEquals(0, $exitCode);

        // 4. Verify citations were generated and target submission count was recalculated to 1
        $this->assertDatabaseHas('citations', [
            'publication_id'       => $pubB->id,
            'target_submission_id' => $subA->id,
            'matched_by'           => 'doi',
        ]);

        $this->assertEquals(1, $subA->fresh()->citation_count);
    }

    public function test_google_scholar_meta_rendering_in_blade_views(): void
    {
        $this->withoutVite();

        // Create an article with 3 references: 2 structured, 1 unstructured
        [$sub, $pub] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Metadata Testing Article for Indexing Engine',
            '10.1234/jcs.meta.1',
            2023,
            [['first_name' => 'Ada', 'last_name' => 'Lovelace']]
        );

        $rawRef1 = "Smith, J. (2021). Alpha Algorithms. Journal of Computing, 1(1), 10-20.";
        $rawRef2 = "Doe, J. (2022). Beta Frameworks. Journal of Systems, 2(2), 30-40.";
        $rawRef3 = "Informal Whitepaper on Future Technology Guidelines";

        $pub->update([
            'references' => implode("\n", [$rawRef1, $rawRef2, $rawRef3])
        ]);

        // Process citations
        ProcessCitationsJob::dispatchSync($pub->id);

        $this->assertCount(3, Citation::where('publication_id', $pub->id)->get());

        // Test journal.public.article view rendering
        $htmlJournal = view('journal.public.article', [
            'journal'         => $this->journalA,
            'article'         => $sub->fresh(['currentPublication.citations', 'currentPublication.authors']),
            'issue'           => $this->issueA,
            'galleys'         => collect(),
            'pdfGalley'       => null,
            'settings'        => [],
            'relatedArticles' => collect(),
        ])->render();

        // 100% of references must be present as citation_reference tags!
        $this->assertEquals(
            3,
            substr_count($htmlJournal, 'name="citation_reference"'),
            'All 3 references must be rendered as citation_reference tags (no selective dropping)'
        );
        $this->assertStringContainsString('citation_title=Alpha Algorithms;', $htmlJournal);
        $this->assertStringContainsString('citation_title=Beta Frameworks;', $htmlJournal);
        $this->assertStringContainsString('content="Informal Whitepaper on Future Technology Guidelines"', $htmlJournal);

        // Test public.article view rendering (verifying $submission variable fix)
        $htmlPublic = view('public.article', [
            'journal'    => $this->journalA,
            'submission' => $sub->fresh(['currentPublication.citations', 'currentPublication.authors']),
            'issue'      => $this->issueA,
            'settings'   => [],
            'pdfGalley'  => null,
        ])->render();

        $this->assertEquals(
            3,
            substr_count($htmlPublic, 'name="citation_reference"'),
            'All 3 references must be rendered in public.article view without undefined variable error'
        );
        $this->assertStringContainsString('citation_title=Alpha Algorithms;', $htmlPublic);
        $this->assertStringContainsString('content="Informal Whitepaper on Future Technology Guidelines"', $htmlPublic);
    }

    public function test_self_citation_is_never_linked(): void
    {
        [$sub, $pub] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Self Citing Author Analysis',
            '10.1234/jcs.self.1',
            2023,
            [['first_name' => 'Self', 'last_name' => 'Citer']]
        );

        // Reference citing itself!
        $pub->update([
            'references' => "Citer, S. (2023). Self Citing Author Analysis. Journal of Computer Science, 1(1), 1-10. https://doi.org/10.1234/jcs.self.1"
        ]);

        ProcessCitationsJob::dispatchSync($pub->id);

        $citation = Citation::where('publication_id', $pub->id)->first();
        $this->assertNotNull($citation);
        $this->assertNull($citation->target_submission_id, 'Self-citation must not link to own submission');
        $this->assertEquals(0, $sub->fresh()->citation_count);
    }

    public function test_unpublished_candidate_is_not_linked(): void
    {
        // Create an accepted (unpublished) article
        $subUnpub = Submission::create([
            'journal_id'   => $this->journalA->id,
            'section_id'   => $this->sectionA->id,
            'user_id'      => $this->user->id,
            'title'        => 'Unpublished Secret Discovery in Science',
            'status'       => Submission::STATUS_ACCEPTED,
            'stage'        => Submission::STAGE_REVIEW,
            'seq_id'       => random_int(10000, 999999),
        ]);

        $pubUnpub = Publication::create([
            'submission_id' => $subUnpub->id,
            'section_id'    => $this->sectionA->id,
            'version'       => 1,
            'status'        => Publication::STATUS_SCHEDULED,
            'title'         => 'Unpublished Secret Discovery in Science',
            'doi'           => '10.1234/jcs.secret.1',
        ]);

        // Citing article referencing the unpublished work
        [$citingSub, $citingPub] = $this->createPublishedArticle(
            $this->journalB, $this->sectionB, $this->issueB,
            'Discussion of Unpublished Work',
            '10.5678/jai.disc.1',
            2024,
            [['first_name' => 'Reviewer', 'last_name' => 'Critic']]
        );

        $citingPub->update([
            'references' => "Secret, U. (2024). Unpublished Secret Discovery in Science. https://doi.org/10.1234/jcs.secret.1"
        ]);

        ProcessCitationsJob::dispatchSync($citingPub->id);

        $citation = Citation::where('publication_id', $citingPub->id)->first();
        $this->assertNotNull($citation);
        $this->assertNull($citation->target_submission_id, 'Unpublished candidate must not be linked');
        $this->assertEquals(0, $subUnpub->fresh()->citation_count);
    }

    public function test_empty_references_handles_gracefully(): void
    {
        [$sub, $pub] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Article Without Any References',
            '10.1234/jcs.empty.1',
            2023,
            [['first_name' => 'Lonely', 'last_name' => 'Writer']]
        );

        $pub->update(['references' => "   \n\n  \t  "]);

        ProcessCitationsJob::dispatchSync($pub->id);

        $this->assertEquals(0, Citation::where('publication_id', $pub->id)->count());
        $this->assertEquals(0, $sub->fresh()->citation_count);
    }

    public function test_unpublishing_article_recalculates_target_citation_count_down(): void
    {
        // 1. Article A in Journal A
        [$subA, $pubA] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Fundamental Theorems of Distributed Computation',
            '10.1234/jcs.distrib.1',
            2023,
            [['first_name' => 'Leslie', 'last_name' => 'Lamport']]
        );

        // 2. Article B in Journal B citing Article A
        [$subB, $pubB] = $this->createPublishedArticle(
            $this->journalB, $this->sectionB, $this->issueB,
            'Consensus in Modern Distributed Ledger Systems',
            '10.5678/jai.consensus.1',
            2024,
            [['first_name' => 'Satoshi', 'last_name' => 'Nakamoto']]
        );

        $pubB->update([
            'references' => "Lamport, L. (2023). Fundamental Theorems of Distributed Computation. https://doi.org/10.1234/jcs.distrib.1"
        ]);

        ProcessCitationsJob::dispatchSync($pubB->id);

        $this->assertEquals(1, $subA->fresh()->citation_count);

        // 3. Now unpublish Article B
        $pubB->update([
            'status' => Publication::STATUS_UNPUBLISHED,
        ]);

        // 4. Article A's citation_count must now be 0!
        $this->assertEquals(0, $subA->fresh()->citation_count, 'Unpublishing citing article must decrement target citation count');
    }

    public function test_short_title_fallback_does_not_falsely_link_all_submissions(): void
    {
        // Article A has a short title <= 8 characters and NO DOI
        [$subA, $pubA] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Review',
            null,
            2023,
            [['first_name' => 'Short', 'last_name' => 'TitleAuthor']]
        );

        // Article B has references that do NOT mention Article A
        [$subB, $pubB] = $this->createPublishedArticle(
            $this->journalB, $this->sectionB, $this->issueB,
            'Completely Unrelated Research Paper',
            '10.5678/jai.unrelated.1',
            2024,
            [['first_name' => 'Another', 'last_name' => 'Researcher']]
        );

        $pubB->update([
            'references' => "OtherAuthor, O. (2020). Completely Different Topic. Nature, 10, 1-5."
        ]);

        // Calling getCitingSubmissions on SubA must NOT return SubB
        $citing = $subA->getCitingSubmissions();
        $this->assertCount(0, $citing, 'Short title without DOI must not falsely match all published submissions in fallback query');
    }

    public function test_multiple_citations_from_same_submission_counts_distinct_citing_submission(): void
    {
        [$subA, $pubA] = $this->createPublishedArticle(
            $this->journalA, $this->sectionA, $this->issueA,
            'Comprehensive Survey of Deep Neural Networks',
            '10.1234/jcs.survey.1',
            2023,
            [['first_name' => 'Alan', 'last_name' => 'Turing']]
        );

        // Article B cites Article A TWICE (via DOI and via Title) in its reference list
        [$subB, $pubB] = $this->createPublishedArticle(
            $this->journalB, $this->sectionB, $this->issueB,
            'Applications of Neural Networks in Robotics',
            '10.5678/jai.robotics.1',
            2024,
            [['first_name' => 'Ada', 'last_name' => 'Lovelace']]
        );

        $pubB->update([
            'references' => "Turing, A. (2023). Comprehensive Survey of Deep Neural Networks. https://doi.org/10.1234/jcs.survey.1\n[2] Turing, A., \"Comprehensive Survey of Deep Neural Networks,\" 2023."
        ]);

        ProcessCitationsJob::dispatchSync($pubB->id);

        // SubA must have citation_count = 1, NOT 2 (distinct citing submission)
        $this->assertEquals(1, $subA->fresh()->citation_count);
        $this->assertCount(1, $subA->getCitingSubmissions());
    }
}

