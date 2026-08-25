<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;
use App\Models\Role;
use App\Models\Journal;
use Illuminate\Support\Facades\Cache;

class ProfileController extends Controller
{
    /**
     * Display user profile edit form.
     * Journal parameter is injected via route model binding.
     */
    public function edit(Request $request, ?Journal $journal = null): View
    {
        $user = Auth::user();
        $availableRoles = collect();
        $userRolesIds = [];
        $enrolledJournalIds = [];
        $userJournalRoles = [];
        $userJournalAdminRoles = [];
        $otherJournals = collect();

        if ($journal) {
            // Fetch roles available for self-registration in current journal
            // Cache self‑registerable roles per journal to avoid repeated queries
            $availableRoles = Cache::remember(
                'available_roles_journal_' . $journal->id,
                now()->addMinutes(30),
                function () use ($journal) {
                    return Role::withoutGlobalScope('journal')
                        ->where('allow_registration', true)
                        ->where(function($query) use ($journal) {
                            $query->where('journal_id', $journal->id)
                                  ->orWhereNull('journal_id');
                        })
                        ->get();
                }
            );

            // Get current user's role names for this journal
            $userRolesNames = $user->journalRoles()
                ->where('journal_id', $journal->id)
                ->with(['role' => function($q) {
                    $q->withoutGlobalScope('journal');
                }])
                ->get()
                ->filter(fn($item) => $item->role)
                ->map(fn($item) => $item->role->name)
                ->toArray();

            // Map names to the rendered available roles IDs for this journal
            $availableRoleIdsByName = $availableRoles->pluck('id', 'name')->toArray();
            foreach ($userRolesNames as $name) {
                if (isset($availableRoleIdsByName[$name])) {
                    $userRolesIds[] = $availableRoleIdsByName[$name];
                }
            }

            // Get all journal IDs where user has a role via JournalUserRole
            // Journals where the user already has a role (for enrollment exclusion)
            $enrolledJournalIds = $user->journalRoles()
                ->pluck('journal_id')
                ->unique()
                ->toArray();

            // Get user's detailed roles in all journals, mapped to correct matching self-registerable role IDs by name
            $userJournalRolesRaw = $user->journalRoles()
                ->with(['role' => function($q) {
                    $q->withoutGlobalScope('journal');
                }])
                ->get()
                ->groupBy('journal_id');

            foreach ($userJournalRolesRaw as $jId => $items) {
                $roleNames = $items->filter(fn($item) => $item->role)->map(fn($item) => $item->role->name)->toArray();
                
                // Get all self-registerable roles for this journal (specific or global)
                $jrRoles = Role::withoutGlobalScope('journal')
                    ->where('journal_id', $jId)
                    ->where('allow_registration', true)
                    ->get();
                    
                if ($jrRoles->isEmpty()) {
                    $jrRoles = Role::withoutGlobalScope('journal')
                        ->whereNull('journal_id')
                        ->where('allow_registration', true)
                        ->get();
                }
                
                $mappedIds = [];
                foreach ($roleNames as $name) {
                    $matchedRole = $jrRoles->firstWhere('name', $name);
                    if ($matchedRole) {
                        $mappedIds[] = $matchedRole->id;
                    }
                }
                $userJournalRoles[$jId] = $mappedIds;
            }

            // Get user's administrative (staff) roles in all journals
            if ($user) {
                $userJournalAdminRoles = $user->journalRoles()
                    ->with(['role' => function($q) {
                        $q->withoutGlobalScope('journal');
                    }])
                    ->get()
                    ->filter(function ($jur) {
                        return $jur->role && !in_array($jur->role->name, ['Author', 'Reader', 'Reviewer', 'Translator']);
                    })
                    ->groupBy('journal_id')
                    ->map(function ($items) {
                        return $items->map(fn($item) => $item->role->name)->unique()->toArray();
                    })
                    ->toArray();
            }

            // Fetch other enabled journals for enrollment
            $query = Journal::where('id', '!=', $journal->id)
                ->where('enabled', true);

            $otherJournals = $query->with(['roles' => function($q) {
                    $q->withoutGlobalScope('journal')->where('allow_registration', true);
                }])
                ->paginate(5)
                ->appends($request->query());
        }

        $activeTab = $request->query('tab', 'identity');

        return view('profile.edit', compact('user', 'journal', 'availableRoles', 'userRolesIds', 'otherJournals', 'enrolledJournalIds', 'userJournalRoles', 'activeTab', 'userJournalAdminRoles'));
    }

    /**
     * Update user profile information.
     */
    public function update(Request $request, ?Journal $journal = null): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            // Identity
            'name' => ['nullable', 'string', 'max:255'],
            'given_name' => ['required', 'string', 'max:255'],
            'family_name' => ['nullable', 'string', 'max:255'],
            'affiliation' => ['nullable', 'string', 'max:500'],
            'country' => ['nullable', 'string', 'max:100'],
            
            // Contact
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]*$/'],
            'mailing_address' => ['nullable', 'string', 'max:1000'],
            
            // Public Profile
            'bio' => ['nullable', 'string', 'max:5000'],
            'homepage' => ['nullable', 'url', 'max:500'],
            'orcid_id' => ['nullable', 'string', 'max:50', 'regex:/^https?:\/\/orcid\.org\/\d{4}-\d{4}-\d{4}-\d{3}[0-9X]$/'],
        ], [
            'orcid_id.regex' => 'The ORCID iD must be a valid URL format (e.g., https://orcid.org/0000-0001-2345-6789)',
            'homepage.url' => 'The homepage must be a valid URL (e.g., https://example.com)',
            'phone.regex' => 'The phone number may only contain numbers, spaces, and the + - ( ) characters.',
        ]);

        // Unset username so it cannot be altered
        unset($validated['username']);

        // Fallback name (Public Name) to Given Name + Family Name if left empty to avoid NOT NULL DB constraint violation
        if (empty(trim($validated['name'] ?? ''))) {
            $validated['name'] = trim($validated['given_name'] . ' ' . ($validated['family_name'] ?? ''));
        }

        // Sanitize Bio HTML (allow basic formatting tags only)
        if (isset($validated['bio'])) {
            $validated['bio'] = $this->sanitizeBio($validated['bio']);
        }

        $user->update($validated);

        return $journal 
            ? redirect()->route('journal.profile.edit', $journal->slug)->with('success', 'Profile updated successfully.')
            : redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    /**
     * Sanitize biography HTML content
     * Allows only safe HTML tags for academic formatting
     */
    private function sanitizeBio(?string $bio): ?string
    {
        if (empty($bio)) {
            return null;
        }

        // Define allowed tags (OJS 3.3 compatible + img for TinyMCE)
        $allowedTags = '<p><br><strong><b><em><i><u><ul><ol><li><a><img>';
        
        // Strip all tags except allowed ones
        $cleaned = strip_tags($bio, $allowedTags);
        
        // Additional security: ensure links only have href attribute
        $cleaned = preg_replace_callback(
            '/<a\s+([^>]*?)>/i',
            function($matches) {
                if (preg_match('/href=["\']([^"\']*?)["\']/i', $matches[1], $href)) {
                    // Only allow http/https links
                    if (preg_match('/^https?:\/\//i', $href[1])) {
                        return '<a href="' . htmlspecialchars($href[1], ENT_QUOTES, 'UTF-8') . '" target="_blank" rel="noopener noreferrer">';
                    }
                }
                return '';
            },
            $cleaned
        );
        
        return $cleaned;
    }

    /**
     * Update user password.
     */
    public function updatePassword(Request $request, ?Journal $journal = null): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
        ]);

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return $journal 
            ? redirect()->route('journal.profile.edit', $journal->slug)->with('success', 'Password updated successfully.')
            : redirect()->route('profile.edit')->with('success', 'Password updated successfully.');
    }

    /**
     * Update user avatar.
     */
    public function updateAvatar(Request $request, ?Journal $journal = null): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ]);

        $user = Auth::user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Store and optimize new avatar
        $file = $request->file('avatar');
        $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
        $path = 'avatars/' . $filename;

        // Resize and optimize the image
        $image = Image::read($file);
        $image->cover(400, 400); // Resize to 400x400
        
        // Save to storage
        Storage::disk('public')->put($path, (string) $image->encode());

        $user->update(['avatar' => $path]);

        return $journal 
            ? redirect()->route('journal.profile.edit', $journal->slug)->with('success', 'Avatar updated successfully.')
            : redirect()->route('profile.edit')->with('success', 'Avatar updated successfully.');
    }

    /**
     * Delete user avatar.
     */
    /**
     * Handle image upload from TinyMCE
     */
    public function uploadImage(Request $request)
    {
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = 'journal_img_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('journal/images', $filename, 'public');
            
            return response()->json([
                'location' => asset('storage/' . $path)
            ]);
        }
        
        return response()->json(['error' => 'No file uploaded'], 400);
    }

    /**
     * Delete user avatar.
     */
    public function deleteAvatar(?Journal $journal = null): RedirectResponse
    {
        $user = Auth::user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return $journal 
            ? redirect()->route('journal.profile.edit', $journal->slug)->with('success', 'Avatar removed successfully.')
            : redirect()->route('profile.edit')->with('success', 'Avatar removed successfully.');
    }

    /**
     * Update user's self-registerable roles safely.
     * CRITICAL SAFETY: This method prevents accidental removal of administrative roles.
     * Algorithm:
     * 1. Keep all existing roles that are NOT allowed for self-registration (e.g., Admin, Editor)
     * 2. Merge with newly selected self-registerable roles
     * 3. Sync the combined list
     */
    public function updateRoles(Request $request, Journal $journal): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'selected_roles' => 'nullable|array',
            'selected_roles.*' => 'exists:roles,id',
        ]);

        // Step 1: Get user's current roles that are NOT allowed for self-registration
        // These are administrative roles (Editor, Journal Manager, etc.) that must be preserved
        // Use JournalUserRole to fetch these accurately
        $keptRoles = $user->journalRoles()
            ->where('journal_id', $journal->id)
            ->whereHas('role', function($query) {
                $query->withoutGlobalScope('journal')
                      ->whereNotIn('name', ['Author', 'Reader', 'Reviewer', 'Translator']);
            })
            ->pluck('role_id')
            ->toArray();

        // Step 2: Get the selected self-registerable roles from the request
        $selectedRoles = $request->input('selected_roles', []);

        // Step 3: Validate that selected roles are actually self-registerable for this journal (specific or global)
        $validSelfRegisterableRoles = Role::withoutGlobalScope('journal')
            ->where('allow_registration', true)
            ->where(function($query) use ($journal) {
                $query->where('journal_id', $journal->id)
                      ->orWhereNull('journal_id');
            })
            ->pluck('id')
            ->toArray();

        $validSelectedRoles = array_intersect($selectedRoles, $validSelfRegisterableRoles);

        // Step 4: Merge kept administrative roles with valid selected roles
        $finalRoles = array_unique(array_merge($keptRoles, $validSelectedRoles));

        // Step 5: Sync only this journal's roles using JournalUserRole logic
        // We delete all existing roles for this user in this journal first
        \App\Models\JournalUserRole::where('journal_id', $journal->id)
            ->where('user_id', $user->id)
            ->delete();

        // Re-assign the final roles
        foreach ($finalRoles as $roleId) {
            \App\Models\JournalUserRole::create([
                'journal_id' => $journal->id,
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]);
        }

        return redirect()->route('journal.profile.edit', $journal->slug)->with('success', 'Your roles have been updated successfully.');
    }

    /**
     * Enroll user in a specific journal with selected roles.
     */
    public function enroll(Request $request, Journal $journal): RedirectResponse
    {
        $request->validate([
            'roles' => 'required|array|min:1',
            'roles.*' => 'string', // Role names like "Author", "Reader"
        ]);

        $user = Auth::user();

        // Get Role IDs for selected role names in THIS journal (specific or global)
        // Ensure they are allowed for self-registration
        $rolesToAssign = Role::withoutGlobalScope('journal')
            ->whereIn('name', $request->roles)
            ->where('allow_registration', true)
            ->where(function($query) use ($journal) {
                $query->where('journal_id', $journal->id)
                      ->orWhereNull('journal_id');
            })
            ->pluck('id')
            ->toArray();

        if (empty($rolesToAssign)) {
            return back()->with('error', 'Invalid roles selected or roles not available for registration.');
        }

        // Attach roles without detaching existing ones (if any)
        // Use JournalUserRole helper or manual create
        foreach ($rolesToAssign as $roleId) {
            \App\Models\JournalUserRole::firstOrCreate([
                'journal_id' => $journal->id,
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]);
        }

        return redirect()->back()->with('success', 'You have successfully joined ' . $journal->name);
    }

    /**
     * Sync user's roles dynamically via AJAX.
     */
    public function syncRolesAjax(Request $request, Journal $journal): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        // Get user's current roles that are NOT allowed for self-registration
        // These are administrative roles (Editor, Journal Manager, etc.) that must be preserved
        $keptRoles = $user->journalRoles()
            ->where('journal_id', $journal->id)
            ->whereHas('role', function($query) {
                $query->withoutGlobalScope('journal')
                      ->whereNotIn('name', ['Author', 'Reader', 'Reviewer', 'Translator']);
            })
            ->pluck('role_id')
            ->toArray();

        // Get the selected self-registerable roles from the request
        $selectedRoles = $request->input('role_ids', []);

        // Validate that selected roles are actually self-registerable for this journal (specific or global)
        $validSelfRegisterableRoles = Role::withoutGlobalScope('journal')
            ->where('allow_registration', true)
            ->where(function($query) use ($journal) {
                $query->where('journal_id', $journal->id)
                      ->orWhereNull('journal_id');
            })
            ->pluck('id')
            ->toArray();

        $validSelectedRoles = array_intersect($selectedRoles, $validSelfRegisterableRoles);

        // Merge kept administrative roles with valid selected roles
        $finalRoles = array_unique(array_merge($keptRoles, $validSelectedRoles));

        // Sync only this journal's roles using JournalUserRole logic
        // Remove all roles for this user in this journal first
        \App\Models\JournalUserRole::where('journal_id', $journal->id)
            ->where('user_id', $user->id)
            ->delete();

        // Re-assign the final roles
        foreach ($finalRoles as $roleId) {
            \App\Models\JournalUserRole::create([
                'journal_id' => $journal->id,
                'user_id' => $user->id,
                'role_id' => $roleId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Roles updated successfully.',
            'enrolled' => count($finalRoles) > 0,
            'roles' => $finalRoles
        ]);
    }
}

