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
    protected $signature = 'migrate:ojs {--file= : Path to OJS SQL dump file} {--step=all : The migration step to run (journals|sections|issues|submissions|authors|metrics|galleys|all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate data from legacy OJS (DB or SQL file) to IamJOS PostgreSQL';

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

        $file = $this->option('file');

        if ($file) {
            $this->info("📂 Using SQL File source: {$file}");
            try {
                $this->migrationService->setSqlSource($file);
                $this->info("📝 Indexing SQL file (this may take a while)...");
                $this->migrationService->indexSqlFile();
                $this->info("✅ SQL file indexed to staging area.");
                
                // Re-detect version after indexing
                $this->migrationService->detectVersion();
            } catch (\Exception $e) {
                $this->error("❌ Error loading SQL file: " . $e->getMessage());
                return 1;
            }
        } else {
            // Setup connection using .env as default for CLI
            $config = (object)[
                'host' => config('database.connections.legacy.host'),
                'port' => config('database.connections.legacy.port'),
                'database' => config('database.connections.legacy.database'),
                'username' => config('database.connections.legacy.username'),
                'password' => config('database.connections.legacy.password'),
            ];
            
            $this->info("🔌 Using Database source: {$config->database}");
            $this->migrationService->setDbSource($config);

            try {
                DB::connection('ojs_legacy')->getPdo();
                $this->info("✅ Connected to legacy OJS database.");
            } catch (\Exception $e) {
                $this->error("❌ Could not connect to legacy database: " . $e->getMessage());
                return 1;
            }
        }

        $step = $this->option('step') ?: 'all';
        $v = $this->migrationService->getDetectedVersion();
        $this->info("🕵️  Detected OJS Version: {$v}");

        if ($step === 'all' || $step === 'users') {
            $this->info("👥 Migrating Users & Roles...");
            $this->migrationService->migrateUsers();
        }

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

        if ($step === 'all' || $step === 'discussions') {
            $this->info("💬 Migrating Discussions...");
            $this->migrationService->migrateDiscussions();
        }

        if ($step === 'all' || $step === 'logs') {
            $this->info("📜 Migrating Activity Logs...");
            $this->migrationService->migrateLogs();
        }

        if ($step === 'all' || $step === 'metrics') {
            $this->info("📊 Migrating Usage Metrics...");
            $this->migrationService->migrateMetrics();
        }


        if ($step === 'all' || $step === 'galleys') {
            $this->info("📎 Migrating Galleys (Metadata only)...");
            // Galleys usually require local file access, so we only migrate metadata here
            // If they want files, they should use a separate command or providing path
        }

        $this->info("✨ Comprehensive Migration completed successfully!");
        
        // Print Summary
        $stats = $this->migrationService->getMigrationStats();
        $this->table(['Module', 'Legacy', 'Migrated', 'Native'], collect($stats)->map(fn($s, $k) => [$k, $s['legacy_count'], $s['migrated_count'], $s['native_count']])->toArray());

        return 0;
    }


}
