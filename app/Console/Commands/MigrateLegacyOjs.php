<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\LegacyMapping;
use App\Models\User;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\Publication;
use App\Models\SubmissionLog;
use App\Models\SubmissionAuthor;
use App\Models\ArticleMetric;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class MigrateLegacyOjs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'iamjos:migrate-ojs {--step=all : The migration step to run (journals|sections|issues|submissions|authors|metrics|all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from legacy OJS MySQL database to IamJOS PostgreSQL';

    protected $migrationService;

    public function __construct(\App\Services\OjsMigrationService $migrationService)
    {
        parent::__construct();
        $this->migrationService = $migrationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🚀 Starting IamJOS Legacy Migration Engine...");

        // Setup connection using .env as default for CLI
        $config = [
            'driver' => 'mysql',
            'host' => config('database.connections.legacy.host'),
            'port' => config('database.connections.legacy.port'),
            'database' => config('database.connections.legacy.database'),
            'username' => config('database.connections.legacy.username'),
            'password' => config('database.connections.legacy.password'),
        ];
        
        $this->migrationService->setupConnection($config);

        try {
            DB::connection('legacy')->getPdo();
            $this->info("✅ Connected to legacy OJS database.");
        } catch (\Exception $e) {
            $this->error("❌ Could not connect to legacy database: " . $e->getMessage());
            return 1;
        }

        $step = $this->option('step') ?: 'all';

        if ($step === 'all' || $step === 'journals') {
            $this->info("📰 Migrating Journals...");
            $this->migrationService->migrateJournals();
        }
        
        if ($step === 'all' || $step === 'sections') {
            $this->info("📂 Migrating Sections...");
            $this->migrationService->migrateSections();
        }

        if ($step === 'all' || $step === 'issues') {
            $this->info("📚 Migrating Issues...");
            $this->migrationService->migrateIssues();
        }

        if ($step === 'all' || $step === 'submissions') {
            $this->info("📄 Migrating Submissions & Publications...");
            $this->migrationService->migrateSubmissions();
        }

        if ($step === 'all' || $step === 'authors') {
            $this->info("✍️ Migrating Authors & Contributors...");
            $this->migrationService->migrateAuthors();
        }

        if ($step === 'all' || $step === 'metrics') {
            $this->info("📊 Migrating Usage Metrics...");
            $this->migrationService->migrateMetrics();
        }

        $this->info("✨ Comprehensive Migration completed successfully!");
    }

}
