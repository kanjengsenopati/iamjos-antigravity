<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;

class UserRouteBindingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_redirects_to_username_when_resolved_with_uuid_on_get_request()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
        ]);

        // Mock the request to be a GET request
        $this->get('/any-url-with-' . $user->id);

        try {
            $user->resolveRouteBinding($user->id);
            $this->fail('Expected HttpResponseException to be thrown');
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $this->assertEquals(301, $response->getStatusCode());
            $this->assertStringContainsString('testuser', $response->headers->get('Location'));
        }
    }

    /** @test */
    public function it_returns_user_directly_when_resolved_with_uuid_on_put_request()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
        ]);

        // Mock the request to be a PUT request
        $this->put('/any-url-with-' . $user->id);

        $resolvedUser = $user->resolveRouteBinding($user->id);

        $this->assertInstanceOf(User::class, $resolvedUser);
        $this->assertEquals($user->id, $resolvedUser->id);
    }

    /** @test */
    public function it_resolves_user_by_username()
    {
        $user = User::factory()->create([
            'username' => 'testuser',
        ]);

        $resolvedUser = $user->resolveRouteBinding('testuser');

        $this->assertInstanceOf(User::class, $resolvedUser);
        $this->assertEquals($user->id, $resolvedUser->id);
    }
}
