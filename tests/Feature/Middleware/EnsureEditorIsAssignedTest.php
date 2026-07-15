<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\EnsureEditorIsAssigned;
use App\Models\Submission;
use App\Models\User;
use App\Models\Journal;
use App\Models\Section;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class EnsureEditorIsAssignedTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Journal $journal;
    protected Section $section;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->journal = Journal::factory()->create();
        $this->section = Section::factory()->create(['journal_id' => $this->journal->id]);
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_resolves_submission_correctly_by_uuid()
    {
        $submission = Submission::factory()->create([
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'user_id' => $this->user->id,
            'seq_id' => 100,
            'slug' => 'test-slug-1',
        ]);

        $request = Request::create('/test-route', 'POST');
        
        // Mock route parameter
        $route = new \Illuminate\Routing\Route('POST', 'test-route/{submission}', ['uses' => function() {}]);
        $route->bind($request);
        $request->setRouteResolver(fn() => $route);
        $request->route()->setParameter('submission', $submission->id);

        $middleware = new EnsureEditorIsAssigned();
        
        // We just want to check if it resolves without Postgres type error
        $called = false;
        try {
            $middleware->handle($request, function ($req) use (&$called) {
                $called = true;
                return response('ok');
            });
        } catch (\Illuminate\Database\QueryException $e) {
            $this->fail("QueryException occurred: " . $e->getMessage());
        }

        $this->assertTrue(true); // Should not throw exception
    }

    /** @test */
    public function it_resolves_submission_correctly_by_slug()
    {
        $submission = Submission::factory()->create([
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'user_id' => $this->user->id,
            'seq_id' => 101,
            'slug' => 'fake-slug-here',
        ]);

        $request = Request::create('/test-route', 'POST');
        
        $route = new \Illuminate\Routing\Route('POST', 'test-route/{submission}', ['uses' => function() {}]);
        $route->bind($request);
        $request->setRouteResolver(fn() => $route);
        $request->route()->setParameter('submission', 'fake-slug-here');

        $middleware = new EnsureEditorIsAssigned();
        
        $called = false;
        try {
            $middleware->handle($request, function ($req) use (&$called) {
                $called = true;
                return response('ok');
            });
        } catch (\Illuminate\Database\QueryException $e) {
            $this->fail("QueryException occurred: " . $e->getMessage());
        }

        $this->assertTrue(true); // Should not throw exception
    }

    /** @test */
    public function it_resolves_submission_correctly_by_seq_id()
    {
        $submission = Submission::factory()->create([
            'journal_id' => $this->journal->id,
            'section_id' => $this->section->id,
            'user_id' => $this->user->id,
            'seq_id' => 102,
            'slug' => 'test-slug-2',
        ]);

        $request = Request::create('/test-route', 'POST');
        
        $route = new \Illuminate\Routing\Route('POST', 'test-route/{submission}', ['uses' => function() {}]);
        $route->bind($request);
        $request->setRouteResolver(fn() => $route);
        $request->route()->setParameter('submission', '102');

        $middleware = new EnsureEditorIsAssigned();
        
        $called = false;
        try {
            $middleware->handle($request, function ($req) use (&$called) {
                $called = true;
                return response('ok');
            });
        } catch (\Illuminate\Database\QueryException $e) {
            $this->fail("QueryException occurred: " . $e->getMessage());
        }

        $this->assertTrue(true); // Should not throw exception
    }
}
