<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $this->get('/login')->assertOk()->assertSee('Bon retour');
    }

    public function test_users_can_login_with_email(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $response = $this->post('/login', [
            'login' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_can_login_with_phone(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $response = $this->post('/login', [
            'login' => $user->phone,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_can_login_with_an_uppercase_email(): void
    {
        $user = User::factory()->create([
            'email' => 'case@example.test',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'login' => 'CASE@example.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_can_login_with_a_formatted_phone(): void
    {
        $user = User::factory()->create([
            'phone' => '+224623000003',
            'password' => 'password123',
        ]);

        $response = $this->post('/login', [
            'login' => '+224 623 000 003',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $user = User::factory()->create(['password' => 'password123']);

        $response = $this->from('/login')->post('/login', [
            'login' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
    }

    public function test_suspended_users_cannot_login(): void
    {
        $user = User::factory()->inactive()->create(['password' => 'password123']);

        $response = $this->from('/login')->post('/login', [
            'login' => $user->email,
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('login');
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('home'));

        $this->assertGuest();
    }

    public function test_guests_are_redirected_from_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee($user->name);
    }

    public function test_dashboard_renders_for_every_role(): void
    {
        foreach ([
            User::factory()->create(),
            User::factory()->worker()->create(),
            User::factory()->admin()->create(),
        ] as $user) {
            $this->actingAs($user)->get('/dashboard')->assertOk();
        }
    }
}
