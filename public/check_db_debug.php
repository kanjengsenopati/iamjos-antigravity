<?php
// Secure with token
if (($_GET['token'] ?? '') !== 'debug_iamjos_sec_2026') {
    die('Unauthorized');
}

header('Content-Type: text/plain');

// Load Composer Autoloader
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel Application
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Submission;

echo "Connected successfully to Laravel App.\n\n";

echo "=== Searching for user Ririn ===\n";
$user = User::where('name', 'like', '%Ririn%')->orWhere('email', 'like', '%ririn%')->first();
if (!$user) {
    echo "User Ririn not found in database.\n";
} else {
    echo "ID: {$user->id}\n";
    echo "Name: {$user->name}\n";
    echo "Email: {$user->email}\n";
    echo "Username: {$user->username}\n\n";

    echo "=== Submissions for this user ===\n";
    $subs = Submission::where('user_id', $user->id)->get();
    if ($subs->isEmpty()) {
        echo "No submissions found for this user in DB.\n";
    } else {
        foreach ($subs as $sub) {
            echo "ID: {$sub->id}\n";
            echo "Title: {$sub->title}\n";
            echo "Status: {$sub->status}\n";
            echo "Stage ID: {$sub->stage_id}\n";
            echo "Journal ID: {$sub->journal_id}\n";
            echo "Created At: {$sub->created_at}\n\n";
        }
    }
}

echo "=== Checking recent submissions ===\n";
$recentSubs = Submission::orderBy('created_at', 'desc')->limit(5)->get();
if ($recentSubs->isEmpty()) {
    echo "No submissions found at all.\n";
} else {
    foreach ($recentSubs as $sub) {
        echo "ID: {$sub->id}\n";
        echo "User ID: {$sub->user_id}\n";
        echo "Title: {$sub->title}\n";
        echo "Journal ID: {$sub->journal_id}\n";
        echo "Created At: {$sub->created_at}\n\n";
    }
}

echo "=== Laravel Log (Last 100 lines) ===\n";
$logFiles = glob(storage_path('logs/laravel*.log'));
if (empty($logFiles)) {
    echo "No log files found.\n";
} else {
    // get latest log file
    usort($logFiles, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });
    $logFile = $logFiles[0];
    echo "Reading log file: " . basename($logFile) . "\n";
    $lines = file($logFile);
    if (!empty($lines)) {
        $lastLines = array_slice($lines, -150);
        echo implode("", $lastLines);
    } else {
        echo "Log file is empty.\n";
    }
}
