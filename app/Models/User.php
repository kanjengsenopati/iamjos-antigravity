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
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_last_login' => 'datetime',
            'date_registered' => 'datetime',
            'privacy_consented_at' => 'datetime',
            'must_change_password' => 'boolean',
            'disabled' => 'boolean',
            'inline_help' => 'boolean',
            'email_notifications' => 'boolean',
        ];
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
     * Get primary role name
     */
    public function getPrimaryRoleAttribute(): string
    {
        return $this->roles->first()?->name ?? 'Member';
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
}
