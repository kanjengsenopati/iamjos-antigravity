<?php

namespace Tests\Feature\Auth;

use App\Models\Journal;
use App\Models\Role;
use App\Models\User;
use App\Models\JournalUserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EnrollTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_other_journals_to_enroll_in_profile_roles_tab(): void
    {
        // 1. Create two journals
        $journalA = Journal::factory()->create(['enabled' => true, 'slug' => 'journal-a']);
        $journalB = Journal::factory()->create(['enabled' => true, 'slug' => 'journal-b']);

        // Seed default roles for both journals
        Role::seedDefaultRolesForJournal($journalA);
        Role::seedDefaultRolesForJournal($journalB);

        // 2. Create a user and enroll in Journal A
        $user = User::factory()->create();
        $authorRoleA = Role::where('journal_id', $journalA->id)->where('name', 'Author')->first();
        
        JournalUserRole::create([
            'journal_id' => $journalA->id,
            'user_id' => $user->id,
            'role_id' => $authorRoleA->id,
        ]);

        // 3. Act as the user
        $response = $this->actingAs($user)
            ->get(route('journal.profile.edit', ['journal' => $journalA->slug, 'tab' => 'roles']));

        // 4. Assert response is success and contains Journal B info
        $response->assertStatus(200);
        $response->assertSee($journalB->name);
        $response->assertSee('Enroll');
    }

    public function test_user_can_enroll_in_other_journal(): void
    {
        // 1. Create two journals
        $journalA = Journal::factory()->create(['enabled' => true, 'slug' => 'journal-a']);
        $journalB = Journal::factory()->create(['enabled' => true, 'slug' => 'journal-b']);

        // Seed default roles for both journals
        Role::seedDefaultRolesForJournal($journalA);
        Role::seedDefaultRolesForJournal($journalB);

        // 2. Create a user and enroll in Journal A
        $user = User::factory()->create();
        $authorRoleA = Role::where('journal_id', $journalA->id)->where('name', 'Author')->first();
        
        JournalUserRole::create([
            'journal_id' => $journalA->id,
            'user_id' => $user->id,
            'role_id' => $authorRoleA->id,
        ]);

        // 3. Post to enroll route for Journal B with roles 'Author' and 'Reader'
        $response = $this->actingAs($user)
            ->post(route('journal.enroll', ['journal' => $journalB->slug]), [
                'roles' => ['Author', 'Reader']
            ]);

        // 4. Assert redirect
        $response->assertRedirect();
        
        // 5. Assert the user now has roles in Journal B in the database
        $authorRoleB = Role::where('journal_id', $journalB->id)->where('name', 'Author')->first();
        $readerRoleB = Role::where('journal_id', $journalB->id)->where('name', 'Reader')->first();

        $this->assertDatabaseHas('journal_user_roles', [
            'journal_id' => $journalB->id,
            'user_id' => $user->id,
            'role_id' => $authorRoleB->id,
        ]);

        $this->assertDatabaseHas('journal_user_roles', [
            'journal_id' => $journalB->id,
            'user_id' => $user->id,
            'role_id' => $readerRoleB->id,
        ]);
    }
}
