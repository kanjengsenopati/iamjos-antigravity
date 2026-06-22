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
        // 1. Create three journals
        $journalA = Journal::factory()->create(['enabled' => true, 'slug' => 'journal-a']);
        $journalB = Journal::factory()->create(['enabled' => true, 'slug' => 'journal-b']);
        $journalC = Journal::factory()->create(['enabled' => true, 'slug' => 'journal-c']);

        // Seed default roles for all journals
        Role::seedDefaultRolesForJournal($journalA);
        Role::seedDefaultRolesForJournal($journalB);
        Role::seedDefaultRolesForJournal($journalC);

        // 2. Create a user
        $user = User::factory()->create();
        
        // Enroll user in Journal A (current journal context)
        $authorRoleA = Role::where('journal_id', $journalA->id)->where('name', 'Author')->first();
        JournalUserRole::create([
            'journal_id' => $journalA->id,
            'user_id' => $user->id,
            'role_id' => $authorRoleA->id,
        ]);

        // Enroll user in Journal C (already enrolled other journal)
        $authorRoleC = Role::where('journal_id', $journalC->id)->where('name', 'Author')->first();
        JournalUserRole::create([
            'journal_id' => $journalC->id,
            'user_id' => $user->id,
            'role_id' => $authorRoleC->id,
        ]);

        // 3. Act as the user on Journal A profile
        $response = $this->actingAs($user)
            ->get(route('journal.profile.edit', ['journal' => $journalA->slug, 'tab' => 'roles']));

        // 4. Assert response is success
        $response->assertStatus(200);

        // Assert Journal B (not enrolled) is visible with "Enroll" button
        $response->assertSee($journalB->name);
        $response->assertSee('Enroll');

        // Assert Journal C (already enrolled) is visible with "Enrolled" badge and "Manage Roles" link
        $response->assertSee($journalC->name);
        $response->assertSee('Enrolled');
        $response->assertSee('Manage Roles');
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
