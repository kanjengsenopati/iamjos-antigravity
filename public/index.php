<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
// DEBUG DUMP
if (isset($_GET['debug_data'])) {
    require __DIR__.'/../vendor/autoload.php';
    $app = require_once __DIR__.'/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    
    $statuses = \App\Models\Submission::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
        ->groupBy('status')
        ->get();
        
    $issues = \App\Models\Issue::select('is_published', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
        ->groupBy('is_published')
        ->get();
        
    header('Content-Type: application/json');
    echo json_encode([
        'statuses' => $statuses,
        'issues' => $issues,
        'db_config' => [
            'database' => config('database.connections.pgsql.database'),
            'host' => config('database.connections.pgsql.host'),
        ]
    ]);
    exit;
}

$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
