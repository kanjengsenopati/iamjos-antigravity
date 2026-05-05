<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$parser = new \App\Services\SqlDumpParserService();
$parser->setFile(storage_path('app/migrations/ojs_dump_1777969010.sql'));

$journals = [];
foreach ($parser->getTableData('journals') as $row) {
    $journals[] = $row;
}
echo "Journals count: " . count($journals) . "\n";

$submissions = [];
foreach ($parser->getTableData('submissions') as $row) {
    $submissions[] = $row;
}
echo "Submissions count: " . count($submissions) . "\n";

$query = "INSERT INTO `submissions` (`submission_id`, `locale`, `context_id`, `section_id`, `language`, `date_submitted`, `last_modified`, `date_status_modified`, `status`, `submission_progress`, `current_publication_id`, `pages`, `hide_author`) VALUES (1, 'en_US', 1, 1, 'en', '2020-01-01 00:00:00', '2020-01-01 00:00:00', '2020-01-01 00:00:00', 3, 0, 1, '1-10', 0), (2, 'en_US', 1, 1, 'en', '2020-01-01 00:00:00', '2020-01-01 00:00:00', '2020-01-01 00:00:00', 3, 0, 2, '11-20', 0);";

// Let's test the regex
$tableName = 'submissions';
$pattern = "/INSERT INTO [\"'`]?" . preg_quote($tableName, '/') . "[\"'`]?\s*(?:\([^)]+\))?\s*VALUES/i";
if (preg_match($pattern, $query)) {
    echo "Pattern matched!\n";
} else {
    echo "Pattern failed!\n";
}
