<?php

namespace App\Jobs;

use App\Models\Citation;
use App\Models\Publication;
use App\Models\Submission;
use App\Services\CitationParserService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Process a publication's raw references into structured citations and link cross-journal citations.
 *
 * This job is dispatched by PublicationObserver when references change or an article is published,
 * or by the citations:process artisan command for bulk processing.
 *
 * 3-Tier Matcher links references to other published submissions on the platform:
 * Tier 1: Clean DOI against published Publication.doi across all journals.
 * Tier 2: Publication year & first author's surname.
 * Tier 3: Normalized alphanumeric title.
 *
 * Automatically recalculates citation_count on targeted submissions.
 *
 * publications.references (source of truth) is NEVER modified by this job.
 */
class ProcessCitationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $publicationId
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $publication = Publication::find($this->publicationId);

        if (!$publication) {
            Log::warning("[ProcessCitationsJob] Publication not found: {$this->publicationId}");
            return;
        }

        // Track previously cited submissions to recalculate their counts if citations change
        $affectedSubmissionIds = Citation::where('publication_id', $publication->id)
            ->whereNotNull('target_submission_id')
            ->pluck('target_submission_id')
            ->unique()
            ->toArray();

        if (empty(trim($publication->references ?? ''))) {
            // No references — clean up any stale citations and recalculate counts
            Citation::where('publication_id', $publication->id)->delete();
            $this->recalculateAffectedCounts($affectedSubmissionIds);
            Log::info("[ProcessCitationsJob] No references for publication {$publication->id}, cleared citations.");
            return;
        }

        // Parse all references into structured data
        $parsedResults = CitationParserService::parseAll($publication->references);

        if (empty($parsedResults)) {
            return;
        }

        // Idempotent: delete existing citations, then re-create
        Citation::where('publication_id', $publication->id)->delete();

        $structuredCount = 0;
        $totalCount = 0;
        $linkedCount = 0;

        foreach ($parsedResults as $parsed) {
            try {
                // 3-Tier Cross-Journal Matching
                $match = $this->matchTargetSubmission($parsed, $publication);
                $targetSubmissionId = $match['target_submission_id'];
                $matchedBy = $match['matched_by'];

                if ($targetSubmissionId) {
                    $linkedCount++;
                    $affectedSubmissionIds[] = $targetSubmissionId;
                }

                Citation::create([
                    'publication_id'       => $publication->id,
                    'target_submission_id' => $targetSubmissionId,
                    'matched_by'           => $matchedBy,
                    'seq'                  => $parsed['seq'],
                    'raw_citation'         => $parsed['raw_citation'],
                    'doi'                  => $parsed['doi'],
                    'title'                => $parsed['title'],
                    'authors'              => $parsed['authors'],
                    'year'                 => $parsed['year'],
                    'source'               => $parsed['source'],
                    'volume'               => $parsed['volume'],
                    'issue'                => $parsed['issue'],
                    'first_page'           => $parsed['first_page'],
                    'last_page'            => $parsed['last_page'],
                    'url'                  => $parsed['url'],
                    'is_structured'        => $parsed['is_structured'],
                    'processing_status'    => 'processed',
                ]);

                $totalCount++;
                if ($parsed['is_structured']) {
                    $structuredCount++;
                }
            } catch (\Exception $e) {
                Log::error("[ProcessCitationsJob] Failed to create citation seq {$parsed['seq']} for pub {$publication->id}: {$e->getMessage()}");
            }
        }

        // Recalculate citation_count on all affected submissions
        $this->recalculateAffectedCounts($affectedSubmissionIds);

        Log::info("[ProcessCitationsJob] Processed {$totalCount} citations for publication {$publication->id} ({$structuredCount} structured, {$linkedCount} linked).");
    }

    /**
     * Match a parsed citation against published publications across all journals.
     *
     * Tier 1: Match by clean DOI against published Publication.doi
     * Tier 2: Narrow candidates by publication year & first author's surname
     * Tier 3: Match normalized alphanumeric title
     *
     * @return array{target_submission_id: ?string, matched_by: ?string}
     */
    /**
     * Cached collection of published candidate publications.
     */
    protected ?\Illuminate\Database\Eloquent\Collection $candidatePublications = null;

    protected function getCandidatePublications(string $sourceSubmissionId): \Illuminate\Database\Eloquent\Collection
    {
        if ($this->candidatePublications === null) {
            $this->candidatePublications = Publication::where('status', Publication::STATUS_PUBLISHED)
                ->where('submission_id', '!=', $sourceSubmissionId)
                ->with(['authors', 'issue'])
                ->get();
        }

        return $this->candidatePublications;
    }

    /**
     * Match a parsed citation against published publications across all journals.
     *
     * Tier 1: Match by clean DOI against published Publication.doi
     * Tier 2: Narrow candidates by publication year & first author's surname
     * Tier 3: Match normalized alphanumeric title
     *
     * @return array{target_submission_id: ?string, matched_by: ?string}
     */
    protected function matchTargetSubmission(array $parsed, Publication $sourcePublication): array
    {
        $sourceSubmissionId = $sourcePublication->submission_id;

        // ── TIER 1: Match by clean DOI ──────────────────────────────────────
        $rawDoi = $parsed['doi'] ?? null;
        if (empty($rawDoi) && !empty($parsed['raw_citation'])) {
            if (preg_match('/\b10\.\d{4,}\/[^\s\]>,;]+/i', $parsed['raw_citation'], $dm)) {
                $rawDoi = $dm[0];
            }
        }

        if (!empty($rawDoi)) {
            $cleanDoi = self::normalizeDoi($rawDoi);

            if (!empty($cleanDoi)) {
                $isPgsql = strtolower(\Illuminate\Support\Facades\DB::connection()->getDriverName()) === 'pgsql';
                $likeOp = $isPgsql ? 'ilike' : 'like';

                // Find published publication with matching DOI across all journals, excluding self
                $matchedPub = Publication::where('status', Publication::STATUS_PUBLISHED)
                    ->where('submission_id', '!=', $sourceSubmissionId)
                    ->whereNotNull('doi')
                    ->where(function ($q) use ($cleanDoi, $likeOp) {
                        $q->whereRaw('LOWER(doi) = ?', [$cleanDoi])
                          ->orWhereRaw('LOWER(doi) = ?', ['https://doi.org/' . $cleanDoi])
                          ->orWhereRaw('LOWER(doi) = ?', ['http://doi.org/' . $cleanDoi])
                          ->orWhere('doi', $likeOp, "%/{$cleanDoi}")
                          ->orWhere('doi', $likeOp, "%{$cleanDoi}%");
                    })
                    ->first();

                if ($matchedPub && $matchedPub->submission_id) {
                    return [
                        'target_submission_id' => $matchedPub->submission_id,
                        'matched_by'           => 'doi',
                    ];
                }
            }
        }

        // ── TIER 2 & TIER 3: Title & Author/Year Matching ──────────────────
        $title = $parsed['title'] ?? null;
        $rawCitation = $parsed['raw_citation'] ?? '';
        $normRef = preg_replace('/[^a-z0-9]/', '', strtolower($rawCitation));

        $normTitle = !empty($title) ? preg_replace('/[^a-z0-9]/', '', strtolower($title)) : null;

        $allCandidates = $this->getCandidatePublications($sourceSubmissionId);

        if ($normTitle && strlen($normTitle) >= 8) {
            $year = $parsed['year'] ?? null;
            $firstAuthorSurname = self::extractSurname($parsed['authors'][0] ?? null);

            // Tier 2: Try narrowing by Year and Author's surname
            if ($year && $firstAuthorSurname) {
                $surnameLower = strtolower($firstAuthorSurname);

                foreach ($allCandidates as $cand) {
                    // Check year match
                    $candYear = $cand->date_published?->year ?? $cand->copyright_year ?? $cand->issue?->year;
                    if ($candYear && (int)$candYear === (int)$year) {
                        // Check author surname match
                        $authorMatched = $cand->authors->contains(function ($author) use ($surnameLower) {
                            $fName = strtolower($author->family_name ?? $author->last_name ?? '');
                            $fullName = strtolower($author->name ?? ($author->first_name . ' ' . $author->last_name));
                            return str_contains($fName, $surnameLower) || str_contains($fullName, $surnameLower);
                        });

                        if ($authorMatched) {
                            $candNorm = preg_replace('/[^a-z0-9]/', '', strtolower($cand->title ?? ''));
                            $candFullNorm = !empty($cand->full_title) ? preg_replace('/[^a-z0-9]/', '', strtolower($cand->full_title)) : null;

                            if ($candNorm === $normTitle || $candFullNorm === $normTitle) {
                                return [
                                    'target_submission_id' => $cand->submission_id,
                                    'matched_by'           => 'author_year_title',
                                ];
                            }
                        }
                    }
                }
            }

            // Tier 3: Match normalized alphanumeric title across published publications
            foreach ($allCandidates as $cand) {
                $candNorm = preg_replace('/[^a-z0-9]/', '', strtolower($cand->title ?? ''));
                if ($candNorm === $normTitle) {
                    return [
                        'target_submission_id' => $cand->submission_id,
                        'matched_by'           => 'title_normalized',
                    ];
                }
                if (!empty($cand->subtitle)) {
                    $candFullNorm = preg_replace('/[^a-z0-9]/', '', strtolower($cand->title . ' ' . $cand->subtitle));
                    if ($candFullNorm === $normTitle) {
                        return [
                            'target_submission_id' => $cand->submission_id,
                            'matched_by'           => 'title_normalized',
                        ];
                    }
                }
            }
        }

        // Tier 3 Fallback: If title was not cleanly parsed, check if raw_citation contains full candidate title
        if (strlen($normRef) >= 20) {
            foreach ($allCandidates as $cand) {
                $candNorm = preg_replace('/[^a-z0-9]/', '', strtolower($cand->title ?? ''));
                if (strlen($candNorm) >= 20 && str_contains($normRef, $candNorm)) {
                    return [
                        'target_submission_id' => $cand->submission_id,
                        'matched_by'           => 'title_normalized',
                    ];
                }
            }
        }

        return [
            'target_submission_id' => null,
            'matched_by'           => null,
        ];
    }

    /**
     * Recalculate citation counts for all affected submission IDs.
     */
    protected function recalculateAffectedCounts(array $submissionIds): void
    {
        $uniqueIds = array_filter(array_unique($submissionIds));
        foreach ($uniqueIds as $subId) {
            try {
                if ($sub = Submission::find($subId)) {
                    $sub->recalculateCitationCount();
                }
            } catch (\Exception $e) {
                Log::error("[ProcessCitationsJob] Failed recalculating citation count for submission {$subId}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Normalize DOI string to bare lowercase DOI.
     */
    public static function normalizeDoi(?string $doi): ?string
    {
        if (empty($doi)) {
            return null;
        }

        $clean = preg_replace('/^https?:\/\/(?:dx\.)?doi\.org\//i', '', trim($doi));
        $clean = preg_replace('/^doi:\s*/i', '', $clean);
        $clean = rtrim($clean, '.,;)"\'');

        return strtolower(trim($clean));
    }

    /**
     * Extract surname from author string.
     */
    public static function extractSurname(?string $authorStr): ?string
    {
        if (empty($authorStr)) {
            return null;
        }

        $authorStr = trim($authorStr);

        if (str_contains($authorStr, ',')) {
            $surname = trim(explode(',', $authorStr)[0]);
        } else {
            $parts = preg_split('/\s+/', $authorStr);
            $surname = end($parts);
        }

        $surname = preg_replace('/[^a-zA-Z]/', '', $surname);

        return mb_strlen($surname) >= 2 ? $surname : null;
    }
}
