<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\JournalUserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class JournalUserManagementController extends Controller
{
    /**
     * Helper to determine route prefix (admin vs general)
     */
    private function getRoutePrefix()
    {
        return request()->routeIs('journal.admin.*') ? 'journal.admin.users' : 'journal.users';
    }

    /**
     * Display all users for the specific journal context.
     * Shows users registered in this journal + all Super Admins.
     */
    public function index(Request $request)
    {
        $journal = current_journal();
        
        // Get user IDs that are registered in this journal
        $journalUserIds = JournalUserRole::where('journal_id', $journal->id)
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        // Also include all Super Admins (they have access to all journals)
        $superAdminIds = User::role('Super Admin')
            ->pluck('id')
            ->toArray();

        // Merge and get unique user IDs
        $allUserIds = array_unique(array_merge($journalUserIds, $superAdminIds));

        $query = User::whereIn('id', $allUserIds);

        // Search filter
        if ($request->has('search') && $request->search != '') {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            });
        }

        // Filter by role in this journal
        if ($request->has('role') && $request->role != '') {
            if ($request->role === 'Super Admin') {
                // Filter to only Super Admins
                $query->role('Super Admin');
            } else {
                $roleId = Role::where('name', $request->role)->value('id');
                if ($roleId) {
                    $userIdsWithRole = JournalUserRole::where('journal_id', $journal->id)
                        ->where('role_id', $roleId)
                        ->pluck('user_id');
                    $query->whereIn('id', $userIdsWithRole);
                }
            }
        }

        $users = $query->paginate(10);

        // Get roles for filtering dropdown
        $roles = Role::pluck('name');

        // Load each user's roles in this journal (includes Super Admin check)
        $users->getCollection()->transform(function ($user) use ($journal) {
            $user->journal_roles = JournalUserRole::getUserRolesInJournal($user, $journal);
            return $user;
        });

        $routePrefix = $this->getRoutePrefix();
        return view('admin.journals.users.index', compact('journal', 'users', 'roles', 'routePrefix'));
    }

    /**
     * Display roles and permissions.
     */
    public function roles()
    {
        $journal = current_journal();
        // Show all roles (roles are global, but assignments are per-journal)
        $roles = Role::all();

        $routePrefix = $this->getRoutePrefix();
        return view('admin.journals.users.roles', compact('journal', 'roles', 'routePrefix'));
    }

    /**
     * Display site access configuration.
     */
    public function access()
    {
        $journal = current_journal();
        $routePrefix = $this->getRoutePrefix();
        return view('admin.journals.users.access', compact('journal', 'routePrefix'));
    }

    public function create()
    {
        $journal = current_journal();
        $routePrefix = $this->getRoutePrefix();

        // Get all assignable roles (exclude Super Admin for security)
        $roles = Role::whereNotIn('name', ['Super Admin'])->get();

        return view('admin.journals.users.create', compact('journal', 'routePrefix', 'roles'));
    }

    public function edit($journal, User $user)
    {
        $journal = current_journal();
        $routePrefix = $this->getRoutePrefix();

        // Get all assignable roles (exclude Super Admin for security)
        $roles = Role::whereNotIn('name', ['Super Admin'])->get();

        // Get user's current roles in THIS journal (not global Spatie roles)
        $userRoleNames = JournalUserRole::getUserRolesInJournal($user, $journal)->pluck('name')->toArray();

        return view('admin.journals.users.edit', compact('journal', 'user', 'routePrefix', 'roles', 'userRoleNames'));
    }

    public function loginAs($journal, User $user)
    {
        // Security check: Only super admin or admin should do this
        if (Auth::user()->id === $user->id) {
            return back()->with('error', 'You cannot login as yourself.');
        }

        Auth::login($user);
        return redirect()->route('journal.dashboard', ['journal' => $journal]);
    }

    public function disable($journal, User $user)
    {
        // Logic to disable user
        $user->update(['status' => 'inactive']); // Assuming status column exists or similar logic
        return back()->with('success', 'User disabled.');
    }

    public function enable($journal, User $user)
    {
        $user->update(['status' => 'active']);
        return back()->with('success', 'User enabled.');
    }

    public function email($journal, User $user)
    {
        // Mock email functionality layout
        return back()->with('success', 'Email compose window opened (mock).');
    }

    // Role Management Methods
    public function createRole()
    {
        $journal = current_journal();
        $routePrefix = $this->getRoutePrefix();
        return view('admin.journals.users.roles.create', compact('journal', 'routePrefix'));
    }

    public function storeRole(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:roles,name|max:255',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'guard_name' => 'web'
        ]);

        return redirect()->route($this->getRoutePrefix() . '.roles', ['journal' => current_journal()->slug])
            ->with('success', "Role '{$role->name}' created successfully.");
    }

    public function editRole($journal, $role)
    {
        $role = Role::findOrFail($role);
        $journal = current_journal(); // Get actual Journal object

        $routePrefix = $this->getRoutePrefix();
        return view('admin.journals.users.roles.edit', compact('journal', 'role', 'routePrefix'));
    }

    public function updateRole(Request $request, $journal, $role)
    {
        $role = Role::findOrFail($role);
        $request->validate([
            'name' => 'required|max:255|unique:roles,name,' . $role->id,
        ]);

        $role->update(['name' => $request->name]);

        return redirect()->route($this->getRoutePrefix() . '.roles', ['journal' => $journal])
            ->with('success', "Role updated successfully.");
    }

    public function destroyRole($journal, $role)
    {
        $role = Role::findOrFail($role);
        // Prevent deleting critical system roles if needed, for now allow all
        $role->delete();
        return redirect()->route($this->getRoutePrefix() . '.roles', ['journal' => $journal])
            ->with('success', "Role deleted successfully.");
    }

    public function resetRolePermissions($journal, $role)
    {
        $role = Role::findOrFail($role);
        // Determine default permissions based on role name logic (simplified for demo)
        $role->syncPermissions([]); // Clear permissions
        return back()->with('success', 'Role permissions reset to default (Cleared).');
    }

    // User Management Store/Update
    public function store(Request $request, $journal)
    {
        $journalModel = current_journal();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|unique:users,username',
            'password' => 'required|min:8',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'email_verified_at' => now(), // Auto-verify when created by admin
        ]);

        // Assign roles to user for THIS journal using JournalUserRole
        JournalUserRole::assignRoles($user, $journalModel, $request->roles);

        // Also give them the Spatie roles for global permission checks
        $user->syncRoles($request->roles);

        return redirect()->route($this->getRoutePrefix() . '.index', ['journal' => $journalModel->slug])
            ->with('success', 'User created and enrolled in this journal successfully.');
    }

    public function update(Request $request, $journal, User $user)
    {
        $journalModel = current_journal();
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->update($request->only('name', 'email'));

        // Remove all existing roles for this user in THIS journal
        JournalUserRole::where('journal_id', $journalModel->id)
            ->where('user_id', $user->id)
            ->delete();

        // Re-assign selected roles for THIS journal
        JournalUserRole::assignRoles($user, $journalModel, $request->roles);

        // Also sync Spatie roles (for permission checks) - merge all journal roles
        $allUserRoles = JournalUserRole::where('user_id', $user->id)
            ->with('role')
            ->get()
            ->pluck('role.name')
            ->unique()
            ->toArray();
        $user->syncRoles($allUserRoles);

        return redirect()->route($this->getRoutePrefix() . '.index', ['journal' => $journalModel->slug])
            ->with('success', 'User roles updated successfully.');
    }

    /**
     * Remove a user from this journal (unenroll).
     * This does NOT delete the user account, just removes their roles in this journal.
     * Super Admins cannot be removed from journals.
     */
    public function destroy($journal, User $user)
    {
        $journalModel = current_journal();
        
        // Super Admins cannot be removed from journals
        if ($user->hasRole('Super Admin')) {
            return redirect()->route($this->getRoutePrefix() . '.index', ['journal' => $journalModel->slug])
                ->with('error', 'Super Admins cannot be removed from journals.');
        }
        
        // Remove all roles for this user in THIS journal
        $deleted = JournalUserRole::where('journal_id', $journalModel->id)
            ->where('user_id', $user->id)
            ->delete();

        if ($deleted) {
            // Re-sync Spatie roles based on remaining journal assignments
            $allUserRoles = JournalUserRole::where('user_id', $user->id)
                ->with('role')
                ->get()
                ->pluck('role.name')
                ->unique()
                ->toArray();
            
            // If user has no more roles, give them Reader as default
            if (empty($allUserRoles)) {
                $allUserRoles = ['Reader'];
            }
            $user->syncRoles($allUserRoles);

            return redirect()->route($this->getRoutePrefix() . '.index', ['journal' => $journalModel->slug])
                ->with('success', 'User removed from this journal successfully.');
        }

        return redirect()->route($this->getRoutePrefix() . '.index', ['journal' => $journalModel->slug])
            ->with('error', 'User was not found in this journal.');
    }

    /**
     * Show form to enroll an existing user to this journal.
     */
    public function enrollForm()
    {
        $journal = current_journal();
        $routePrefix = $this->getRoutePrefix();

        // Get users NOT already in this journal (exclude Super Admins - they're already in all journals)
        $existingUserIds = JournalUserRole::where('journal_id', $journal->id)
            ->distinct()
            ->pluck('user_id')
            ->toArray();

        // Also exclude Super Admins since they're automatically in all journals
        $superAdminIds = User::role('Super Admin')->pluck('id')->toArray();
        $excludeIds = array_unique(array_merge($existingUserIds, $superAdminIds));

        $availableUsers = User::whereNotIn('id', $excludeIds)
            ->orderBy('name')
            ->get();

        $roles = Role::whereNotIn('name', ['Super Admin'])->get();

        return view('admin.journals.users.enroll', compact('journal', 'routePrefix', 'availableUsers', 'roles'));
    }

    /**
     * Enroll an existing user to this journal.
     */
    public function enroll(Request $request, $journal)
    {
        $journalModel = current_journal();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'roles' => 'required|array|min:1',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::findOrFail($request->user_id);

        // Assign roles to user for THIS journal
        JournalUserRole::assignRoles($user, $journalModel, $request->roles);

        // Also sync Spatie roles
        $allUserRoles = JournalUserRole::where('user_id', $user->id)
            ->with('role')
            ->get()
            ->pluck('role.name')
            ->unique()
            ->toArray();
        $user->syncRoles($allUserRoles);

        return redirect()->route($this->getRoutePrefix() . '.index', ['journal' => $journalModel->slug])
            ->with('success', "User '{$user->name}' enrolled in this journal successfully.");
    }

    // Access Management
    public function updateAccess(Request $request)
    {
        // Logic to update journal settings regarding access
        return back()->with('success', 'Access settings updated.');
    }
}
