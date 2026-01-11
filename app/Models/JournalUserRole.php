<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalUserRole extends Model
{
    use HasFactory, HasUuids;

    /**
     * The table associated with the model.
     */
    protected $table = 'journal_user_roles';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'journal_id',
        'user_id',
        'role_id',
    ];

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the journal for this assignment.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(Journal::class);
    }

    /**
     * Get the user for this assignment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the role for this assignment.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    // =====================================================
    // STATIC HELPERS
    // =====================================================

    /**
     * Assign a role to a user for a specific journal.
     *
     * @param User $user
     * @param Journal|string $journal Journal model or UUID
     * @param Role|string $role Role model or role name
     * @return self
     */
    public static function assignRole(User $user, $journal, $role): self
    {
        $journalId = $journal instanceof Journal ? $journal->id : $journal;
        
        if (is_string($role) && !preg_match('/^[0-9a-f-]{36}$/i', $role)) {
            // It's a role name, find the role
            $role = Role::where('name', $role)->first();
        }
        
        $roleId = $role instanceof Role ? $role->id : $role;

        return self::firstOrCreate([
            'journal_id' => $journalId,
            'user_id' => $user->id,
            'role_id' => $roleId,
        ]);
    }

    /**
     * Assign multiple roles to a user for a specific journal.
     *
     * @param User $user
     * @param Journal|string $journal
     * @param array $roles Array of Role models or role names
     * @return void
     */
    public static function assignRoles(User $user, $journal, array $roles): void
    {
        foreach ($roles as $role) {
            self::assignRole($user, $journal, $role);
        }
    }

    /**
     * Remove a role from a user for a specific journal.
     *
     * @param User $user
     * @param Journal|string $journal
     * @param Role|string $role
     * @return bool
     */
    public static function removeRole(User $user, $journal, $role): bool
    {
        $journalId = $journal instanceof Journal ? $journal->id : $journal;
        
        if (is_string($role) && !preg_match('/^[0-9a-f-]{36}$/i', $role)) {
            $role = Role::where('name', $role)->first();
        }
        
        $roleId = $role instanceof Role ? $role->id : $role;

        return self::where([
            'journal_id' => $journalId,
            'user_id' => $user->id,
            'role_id' => $roleId,
        ])->delete() > 0;
    }

    /**
     * Check if a user has a specific role in a journal.
     *
     * @param User $user
     * @param Journal|string $journal
     * @param Role|string $role
     * @return bool
     */
    public static function hasRole(User $user, $journal, $role): bool
    {
        $journalId = $journal instanceof Journal ? $journal->id : $journal;
        
        if (is_string($role) && !preg_match('/^[0-9a-f-]{36}$/i', $role)) {
            $role = Role::where('name', $role)->first();
            if (!$role) return false;
        }
        
        $roleId = $role instanceof Role ? $role->id : $role;

        return self::where([
            'journal_id' => $journalId,
            'user_id' => $user->id,
            'role_id' => $roleId,
        ])->exists();
    }

    /**
     * Get all journals a user is registered with.
     *
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUserJournals(User $user)
    {
        $journalIds = self::where('user_id', $user->id)
            ->distinct()
            ->pluck('journal_id');

        return Journal::whereIn('id', $journalIds)->get();
    }

    /**
     * Get all roles a user has in a specific journal.
     *
     * @param User $user
     * @param Journal|string $journal
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getUserRolesInJournal(User $user, $journal)
    {
        $journalId = $journal instanceof Journal ? $journal->id : $journal;

        $roleIds = self::where([
            'journal_id' => $journalId,
            'user_id' => $user->id,
        ])->pluck('role_id');

        return Role::whereIn('id', $roleIds)->get();
    }
}
