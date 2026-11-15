<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'role:admin'])
            ->get('/_test/admin', fn () => 'admin-only');
    }

    public function test_admins_can_access_role_protected_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get('/_test/admin')
            ->assertOk()
            ->assertSee('admin-only');
    }

    public function test_customers_are_forbidden_from_admin_routes(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)
            ->get('/_test/admin')
            ->assertForbidden();
    }

    public function test_guests_are_redirected_before_role_check(): void
    {
        $this->get('/_test/admin')->assertRedirect(route('login'));
    }
}
