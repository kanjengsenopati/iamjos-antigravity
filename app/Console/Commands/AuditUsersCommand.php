<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditUsersCommand extends Command
{
    protected $signature = 'audit:users';
    protected $description = 'Audit users for duplicate emails and spaces';

    public function handle()
    {
        $output = [];
        $output['total_users'] = DB::table('users')->count();
        $output['soft_deleted_users'] = DB::table('users')->whereNotNull('deleted_at')->count();
        $output['active_users'] = DB::table('users')->whereNull('deleted_at')->count();

        $duplicates = DB::table('users')
            ->select('email', DB::raw('count(*) as count'))
            ->groupBy('email')
            ->havingRaw('count(*) > 1')
            ->get();
        $output['emails_with_duplicates_in_db'] = $duplicates;

        $spaces = DB::table('users')
            ->where('email', 'like', ' %')
            ->orWhere('email', 'like', '% ')
            ->get(['id', 'email', 'deleted_at']);
        $output['emails_with_spaces'] = $spaces;

        $deletedEmails = DB::table('users')->whereNotNull('deleted_at')->pluck('email')->toArray();
        $activeEmails = DB::table('users')->whereNull('deleted_at')->pluck('email')->toArray();
        $output['emails_in_both_deleted_and_active'] = array_values(array_intersect($deletedEmails, $activeEmails));

        echo json_encode($output, JSON_PRETTY_PRINT);
    }
}
