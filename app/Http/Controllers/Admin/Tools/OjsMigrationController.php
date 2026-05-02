<?php

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Models\LegacySourceConfig;
use App\Services\OjsMigrationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OjsMigrationController extends Controller
{
    protected $migrationService;

    public function __construct(OjsMigrationService $migrationService)
    {
        $this->migrationService = $migrationService;
    }

    /**
     * Dashboard Index
     */
    public function index()
    {
        $config = LegacySourceConfig::where('is_active', true)->first();
        $stats = [];
        $connectionError = null;

        if ($config) {
            try {
                $this->migrationService->setupConnection([
                    'driver' => $config->driver,
                    'host' => $config->host,
                    'port' => $config->port,
                    'database' => $config->database,
                    'username' => $config->username,
                    'password' => $config->password,
                ]);
                $stats = $this->migrationService->getMigrationStats();
            } catch (\Exception $e) {
                $connectionError = $e->getMessage();
            }
        }

        return view('admin.tools.migration.index', compact('config', 'stats', 'connectionError'));
    }

    /**
     * Store Configuration
     */
    public function storeConfig(Request $request)
    {
        $request->validate([
            'host' => 'required',
            'database' => 'required',
            'username' => 'required',
            'password' => 'nullable',
        ]);


        LegacySourceConfig::updateOrCreate(
            ['is_active' => true],
            [
                'host' => $request->host,
                'port' => $request->port ?? '3306',
                'database' => $request->database,
                'username' => $request->username,
                'password' => $request->password,
            ]
        );

        return back()->with('success', 'Konfigurasi database legacy berhasil disimpan.');
    }

    /**
     * Run Migration Step
     */
    public function runStep(Request $request)
    {
        $step = $request->step;
        $config = LegacySourceConfig::where('is_active', true)->first();

        if (!$config) {
            return response()->json(['success' => false, 'message' => 'Konfigurasi belum diset.']);
        }

        $this->migrationService->setupConnection([
            'driver' => $config->driver,
            'host' => $config->host,
            'port' => $config->port,
            'database' => $config->database,
            'username' => $config->username,
            'password' => $config->password,
        ]);

        try {
            switch ($step) {
                case 'journals': $this->migrationService->migrateJournals(); break;
                case 'sections': $this->migrationService->migrateSections(); break;
                case 'issues': $this->migrationService->migrateIssues(); break;
                case 'submissions': $this->migrationService->migrateSubmissions(); break;
                case 'authors': $this->migrationService->migrateAuthors(); break;
                case 'metrics': $this->migrationService->migrateMetrics(); break;
                default: throw new \Exception("Step tidak valid.");
            }

            return response()->json(['success' => true, 'message' => "Migrasi step $step berhasil."]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
