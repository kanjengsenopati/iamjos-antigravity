<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, HasUuids, HasRoles, SoftDeletes;

    /**
     * Default guard for Spatie Permission
     */
    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'username',
        'given_name',
        'family_name',
        'email',
        'password',
        'affiliation',
        'country',
        'bio',
        'phone',
        'avatar',
        'orcid_id',
        'homepage',
        'mailing_address',
        'signature',
        'locale',
        'date_last_login',
        'date_registered',
        'must_change_password',
        'disabled',
        'disabled_reason',
        'inline_help',
        'email_notifications',
        'privacy_consented_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get submissions authored by this user
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'user_id');
    }

    /**
     * Get review assignments for this user (as reviewer)
     */
    public function reviewAssignments(): HasMany
    {
        return $this->hasMany(ReviewAssignment::class, 'reviewer_id');
    }

    /**
     * Get uploaded files by this user
     */
    public function uploadedFiles(): HasMany
    {
        return $this->hasMany(SubmissionFile::class, 'uploaded_by');
    }

    /**
     * Get journal-specific role assignments for this user
     */
    public function journalRoles(): HasMany
    {
        return $this->hasMany(JournalUserRole::class, 'user_id');
    }

    /**
     * Get journals this user is registered with
     */
    public function registeredJournals()
    {
        return JournalUserRole::getUserJournals($this);
    }

    /**
     * Get user's roles in a specific journal
     */
    public function rolesInJournal($journal)
    {
        return JournalUserRole::getUserRolesInJournal($this, $journal);
    }

    /**
     * Check if user has a specific role in a journal
     */
    public function hasRoleInJournal($role, $journal): bool
    {
        return JournalUserRole::hasRole($this, $journal, $role);
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Cek Permission Level (Angka).
     * @param int|array $levels
     * @param string $journalId
     */
    public function hasJournalPermission($levels, $journalId)
    {
        // 1. Bypass Super Admin
        if (method_exists($this, 'hasRole') && $this->hasRole('Super Admin')) {
            return true;
        }

        $levels = is_array($levels) ? $levels : [$levels];

        return DB::table('journal_user_roles')
            ->join('roles', 'journal_user_roles.role_id', '=', 'roles.id')
            
            ->where('journal_user_roles.user_id', $this->id)
            
            ->where('journal_user_roles.journal_id', $journalId) 
            
            ->whereIn('roles.permission_level', $levels)
            ->exists();
    }

    /**
     * Smart Check (Bisa Terima Nama Role atau Level Permission).
     * @param string|int|array $roles
     * @param string $journalId
     */
    public function hasJournalRole($roles, $journalId)
    {
        // --- 0. BYPASS SUPER ADMIN (Tambahan) ---
        // Agar Super Admin selalu TRUE walau dicek pakai String ('Editor')
        if (method_exists($this, 'hasRole') && $this->hasRole('Super Admin')) {
            return true;
        }

        // 1. Normalisasi String pipa "|"
        if (is_string($roles)) {
            $roles = explode('|', $roles);
        }
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        // 2. DETEKSI TIPE INPUT
        $firstItem = reset($roles);

        // Jika inputnya Angka, oper ke fungsi permission level
        if (is_numeric($firstItem)) {
            return $this->hasJournalPermission($roles, $journalId);
        }

        // 3. Jika inputnya String (Nama Role), jalankan query by NAME
        return DB::table('journal_user_roles')
            ->join('roles', 'journal_user_roles.role_id', '=', 'roles.id')
            ->where('journal_user_roles.user_id', $this->id)
            
            // PENTING: Sudah benar pakai tabel pivot untuk filter jurnal
            ->where('journal_user_roles.journal_id', $journalId) 
            
            ->whereIn('roles.name', $roles)
            ->exists();
    }
    /**
     * Get the user's initials
     */
    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';

        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }

        return substr($initials, 0, 2);
    }

    /**
     * Get primary role name (legacy - for backward compatibility)
     */
    public function getPrimaryRoleAttribute(): string
    {
        return $this->primary_role_label;
    }

    /**
     * Get the highest priority role name for display.
     * 
     * This accessor implements smart role selection where higher privilege
     * roles take precedence. Reader is only shown if no other roles exist.
     */
    public function getPrimaryRoleLabelAttribute(): string
    {
        // Get all user roles (case-insensitive comparison)
        $userRoles = $this->roles->pluck('name')->map(fn($name) => strtolower($name))->toArray();

        // Define priority (check from most important to least)
        // Order matters! First match wins.
        $priorityList = [
            'super admin' => 'Super Admin',
            'admin' => 'Admin',
            'journal manager' => 'Journal Manager',
            'editor' => 'Editor',
            'section editor' => 'Section Editor',
            'guest editor' => 'Guest Editor',
            'copyeditor' => 'Copyeditor',
            'layout editor' => 'Layout Editor',
            'proofreader' => 'Proofreader',
            'reviewer' => 'Reviewer',
            'author' => 'Author',
            'reader' => 'Reader', // Lowest priority - only if nothing else
        ];

        foreach ($priorityList as $key => $label) {
            if (in_array($key, $userRoles, true)) {
                return $label;
            }
        }

        // Fallback: return first role if exists, otherwise 'Guest'
        return $this->roles->first()?->name ?? 'Guest';
    }

    /**
     * Get avatar URL
     */
    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return \Illuminate\Support\Facades\Storage::url($this->avatar);
        }
        return null;
    }

    public function submissionAuthors()
    {
        return $this->hasMany(SubmissionAuthor::class, 'user_id');
    }
}
