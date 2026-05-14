<?php
/**
 * UI and logic for importing and exporting user data for the current journal.
 */

namespace App\Http\Controllers\Admin\Tools;

use App\Http\Controllers\Controller;
use App\Models\JournalUserRole;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserImportExportController extends Controller
{
    /**
     * Display the Users XML Plugin page.
     */
    public function index()
    {
        $journal = current_journal();

        if (!$journal) {
            abort(404, 'Journal not found');
        }

        // Fetch users in this journal for export
        $userIds = JournalUserRole::where('journal_id', $journal->id)
            ->distinct()
            ->pluck('user_id');

        $users = User::whereIn('id', $userIds)
            ->with(['roles' => function($q) use ($journal) {
                $q->where('journal_id', $journal->id);
            }])
            ->paginate(50);

        return view('manager.tools.importexport.users', compact('journal', 'users'));
    }

    /**
     * Export selected users as XML.
     */
    public function export(Request $request): StreamedResponse
    {
        $journal = current_journal();
        $ids = $request->input('user_ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Please select at least one user to export.');
        }

        $users = User::whereIn('id', $ids)
            ->with(['roles' => function($q) use ($journal) {
                $q->where('journal_id', $journal->id);
            }])
            ->get();

        $filename = 'users-' . date('Ymd-His') . '.xml';

        return response()->streamDownload(function () use ($users, $journal) {
            $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><users/>');
            
            foreach ($users as $user) {
                $userNode = $xml->addChild('user');
                $userNode->addChild('username', htmlspecialchars($user->username));
                $userNode->addChild('email', htmlspecialchars($user->email));
                $userNode->addChild('name', htmlspecialchars($user->name));
                $userNode->addChild('given_name', htmlspecialchars($user->given_name));
                $userNode->addChild('family_name', htmlspecialchars($user->family_name));
                $userNode->addChild('affiliation', htmlspecialchars($user->affiliation ?? ''));
                $userNode->addChild('country', htmlspecialchars($user->country ?? ''));
                $userNode->addChild('orcid', htmlspecialchars($user->orcid_id ?? ''));
                $userNode->addChild('bio', htmlspecialchars(strip_tags($user->bio ?? '')));
                
                $rolesNode = $userNode->addChild('roles');
                foreach ($user->roles as $role) {
                    $rolesNode->addChild('role', htmlspecialchars($role->name));
                }
            }

            echo $xml->asXML();
        }, $filename, ['Content-Type' => 'text/xml']);
    }

    /**
     * Import users from XML file.
     */
    public function import(Request $request)
    {
        $journal = current_journal();

        $request->validate([
            'xml_file' => 'required|file|mimes:xml,text|max:10240',
        ]);

        try {
            $content = file_get_contents($request->file('xml_file')->getRealPath());
            $xml = new SimpleXMLElement($content);

            DB::beginTransaction();
            $processedCount = 0;

            foreach ($xml->user as $userNode) {
                $email = (string) $userNode->email;
                
                // Find or Create User
                $user = User::where('email', $email)->first();
                
                if (!$user) {
                    $user = User::create([
                        'username' => (string) $userNode->username,
                        'email' => $email,
                        'name' => (string) $userNode->name,
                        'given_name' => (string) $userNode->given_name,
                        'family_name' => (string) ($userNode->family_name ?? ''),
                        'affiliation' => (string) ($userNode->affiliation ?? ''),
                        'country' => (string) ($userNode->country ?? ''),
                        'orcid_id' => (string) ($userNode->orcid ?? ''),
                        'bio' => (string) ($userNode->bio ?? ''),
                        'password' => bcrypt('password'), // Default
                    ]);
                }

                // Assign Roles for current journal
                if (isset($userNode->roles->role)) {
                    foreach ($userNode->roles->role as $roleName) {
                        $role = Role::where('journal_id', $journal->id)
                            ->where('name', (string) $roleName)
                            ->first();
                        
                        if ($role) {
                            JournalUserRole::firstOrCreate([
                                'journal_id' => $journal->id,
                                'user_id' => $user->id,
                                'role_id' => $role->id,
                            ]);
                        }
                    }
                }

                $processedCount++;
            }

            DB::commit();
            return back()->with('success', "Successfully processed $processedCount users.");
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User XML Import Error: ' . $e->getMessage());
            return back()->with('error', 'Import Failed: ' . $e->getMessage());
        }
    }
}
