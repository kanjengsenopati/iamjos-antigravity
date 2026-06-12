<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Journal;

try {
    $journal = Journal::create([
        'name' => 'Journal of Kampus SaaS Test',
        'path' => 'saas-test',
        'slug' => 'saas-test',
        'abbreviation' => 'KST',
        'enabled' => true,
        'visible' => true,
    ]);
    echo "Journal inserted successfully with ID: " . $journal->id . "\n";
} catch (\Exception $e) {
    echo "Error inserting journal: " . $e->getMessage() . "\n";
}
