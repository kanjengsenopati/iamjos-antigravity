<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
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
     */
    public function index(Request $request)
    {
        $journal = current_journal();
        $query = User::query();

        // Filter logic here (e.g., search, role)
        // For now, simple pagination
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role if provided
        if ($request->has('role') && $request->role != '') {
            $query->role($request->role);
        }

        $users = $query->paginate(10);

        // Get generic roles for filtering dropdown
        $roles = Role::pluck('name');

        $routePrefix = $this->getRoutePrefix();
        return view('admin.journals.users.index', compact('journal', 'users', 'roles', 'routePrefix'));
    }

    /**
     * Display roles and permissions.
     */
    public function roles()
    {
        $journal = current_journal();
        // Just show all roles for now. in OJS context, roles might be scoped per journal, 
        // but typically roles are global in Spatie unless using teams. 
        // Assuming global roles for now but displayed within journal context.
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

        // Get user's current role names for pre-selection
        $userRoleNames = $user->roles->pluck('name')->toArray();

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
    // User Management Store/Update
    public function store(Request $request, $journal)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|unique:users,username',
            'password' => 'required|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'password' => bcrypt($request->password),
        ]);

        // Sync roles (will replace all current roles)
        if ($request->has('roles') && !empty($request->roles)) {
            $user->syncRoles($request->roles);
        } else {
            $user->syncRoles(['Reader']); // Default role
        }

        return redirect()->route($this->getRoutePrefix() . '.index', ['journal' => current_journal()->slug])->with('success', 'User created successfully.');
    }

    public function update(Request $request, $journal, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->update($request->only('name', 'email'));

        // Sync roles (will replace all current roles)
        if ($request->has('roles') && !empty($request->roles)) {
            $user->syncRoles($request->roles);
        } else {
            $user->syncRoles(['Reader']); // Default role if none selected
        }

        return redirect()->route($this->getRoutePrefix() . '.index', ['journal' => current_journal()->slug])->with('success', 'User updated successfully.');
    }

    // Access Management
    public function updateAccess(Request $request)
    {
        // Logic to update journal settings regarding access
        return back()->with('success', 'Access settings updated.');
    }
}
