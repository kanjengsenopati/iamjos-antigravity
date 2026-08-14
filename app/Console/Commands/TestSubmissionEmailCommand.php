<?php

namespace App\Console\Commands;

use App\Facades\Settings;
use App\Models\Journal;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\SubmissionReceived;
use App\Notifications\NewSubmissionNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class TestSubmissionEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:submission-email {email? : Destination email address to test} {--journal= : Journal slug or ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Empirically test submission email notifications and SMTP delivery';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("====================================================");
        $this->info("   IAMJOS SUBMISSION EMAIL EMPIRICAL DIAGNOSTIC     ");
        $this->info("====================================================");

        // 1. Check System Settings
        $this->newLine();
        $this->line("<fg=yellow;options=bold>1. Checking Database System Settings (Mail):</>");
        $mailer = Settings::system('mail_mailer') ?? config('mail.default');
        $host = Settings::system('mail_host') ?? config('mail.mailers.smtp.host');
        $port = Settings::system('mail_port') ?? config('mail.mailers.smtp.port');
        $user = Settings::system('mail_username') ?? config('mail.mailers.smtp.username');
        $enc = Settings::system('mail_encryption') ?? 'none';
        $from = Settings::system('mail_from_address') ?? config('mail.from.address');
        $fromName = Settings::system('mail_from_name') ?? config('mail.from.name');
        $queue = Settings::system('mail_queue_connection') ?? config('queue.default');

        $this->table(
            ['Key', 'Current Active Value'],
            [
                ['mail_mailer', $mailer],
                ['mail_host', $host],
                ['mail_port', $port],
                ['mail_username', $user],
                ['mail_encryption', $enc],
                ['mail_from_address', $from],
                ['mail_from_name', $fromName],
                ['mail_queue_connection', $queue],
            ]
        );

        // 2. Determine Recipient
        $destination = $this->argument('email') ?: $from;
        if (!$destination) {
            $this->error("No destination email provided and mail_from_address is empty!");
            return 1;
        }

        // 3. Socket Connectivity Check
        $this->newLine();
        $this->line("<fg=yellow;options=bold>2. Testing Socket Connection to SMTP Host:</>");
        $scheme = ($enc === 'ssl' || (int)$port === 465) ? 'ssl://' : '';
        $target = "{$scheme}{$host}:{$port}";
        $this->line("   Connecting to: {$target} ...");
        
        $errno = 0;
        $errstr = '';
        $socket = @fsockopen("{$scheme}{$host}", (int)$port, $errno, $errstr, 10);
        if ($socket) {
            $greeting = trim(fgets($socket, 512));
            $this->info("   [SUCCESS] Connected! Server: {$greeting}");
            fclose($socket);
        } else {
            $this->error("   [FAILED] Unable to reach SMTP socket ({$errno}): {$errstr}");
        }

        // 4. Submission & Journal Context
        $this->newLine();
        $this->line("<fg=yellow;options=bold>3. Loading Journal & Submission Context:</>");
        $journalSlug = $this->option('journal');
        if ($journalSlug) {
            $journal = \Illuminate\Support\Str::isUuid($journalSlug)
                ? Journal::where('id', $journalSlug)->first()
                : Journal::where('slug', $journalSlug)->first();
        } else {
            $journal = Journal::first();
        }
        
        if (!$journal) {
            $this->warn("   No journal found in database. Using mock journal object.");
            $journal = new Journal([
                'name' => 'Tawazun: Jurnal Kajian Islam',
                'slug' => 'tawazun',
                'abbreviation' => 'TAWAZUN',
            ]);
        } else {
            $this->info("   Journal Context: [{$journal->name}] (Slug: {$journal->slug})");
        }

        $submission = Submission::where('journal_id', $journal->id)->latest()->first();
        if (!$submission) {
            $this->warn("   No submission found for this journal. Using mock submission.");
            $submission = new Submission([
                'id' => '00000000-0000-0000-0000-000000000001',
                'seq_id' => 999,
                'title' => 'Empirical Article Submission Test',
                'status' => Submission::STATUS_SUBMITTED,
                'stage' => Submission::STAGE_SUBMISSION,
                'submitted_at' => now(),
            ]);
            $submission->setRelation('journal', $journal);
        } else {
            $this->info("   Submission Context: #{$submission->seq_id} - '{$submission->title}'");
        }

        // 5. Test Live Mail Sending
        $this->newLine();
        $this->line("<fg=yellow;options=bold>4. Testing Live Notification Dispatch:</>");
        
        // 5.1 Author Notification (SubmissionReceived)
        $this->line("   [A] Testing SubmissionReceived notification -> <fg=cyan>{$destination}</> ...");
        try {
            // Apply current mail config fresh
            if (app()->resolved('mail.manager')) {
                app()->make('mail.manager')->forgetMailers();
            }
            Notification::route('mail', $destination)->notify(new SubmissionReceived($submission));
            $this->info("       [OK] SubmissionReceived email sent successfully!");
        } catch (\Throwable $e) {
            $this->error("       [ERROR] " . $e->getMessage());
        }

        // 5.2 Editor Notification (NewSubmissionNotification)
        $this->line("   [B] Testing NewSubmissionNotification notification -> <fg=cyan>{$destination}</> ...");
        try {
            Notification::route('mail', $destination)->notify(new NewSubmissionNotification($submission));
            $this->info("       [OK] NewSubmissionNotification email sent successfully!");
        } catch (\Throwable $e) {
            $this->error("       [ERROR] " . $e->getMessage());
        }

        $this->newLine();
        $this->info("====================================================");
        $this->info("              DIAGNOSTIC COMPLETE                   ");
        $this->info("====================================================");

        return 0;
    }
}
