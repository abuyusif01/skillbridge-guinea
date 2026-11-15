<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\Trade;
use App\Models\User;
use Database\Seeders\TradeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_can_register(): void
    {
        $response = $this->post('/register', $this->customerPayload());

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'new.customer@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::Customer, $user->role);
        $this->assertNull($user->workerProfile);
    }

    public function test_workers_can_register_with_a_profile(): void
    {
        $this->seed(TradeSeeder::class);
        $trade = Trade::where('slug', 'electrician')->firstOrFail();

        $response = $this->post('/register', $this->customerPayload([
            'name' => 'New Worker',
            'email' => 'new.worker@example.test',
            'phone' => '+224622000099',
            'role' => 'worker',
            'trade_id' => $trade->id,
            'experience_years' => 6,
            'service_area' => 'Kaloum, Matoto',
            'bio' => 'Installations et dépannages électriques.',
        ]));

        $response->assertRedirect(route('dashboard'));

        $user = User::where('email', 'new.worker@example.test')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::Worker, $user->role);
        $this->assertDatabaseHas('worker_profiles', [
            'user_id' => $user->id,
            'trade_id' => $trade->id,
            'experience_years' => 6,
        ]);
        $this->assertNull($user->workerProfile->verified_at);
    }

    public function test_admin_role_cannot_be_self_assigned(): void
    {
        $response = $this->from('/register')->post('/register', $this->customerPayload([
            'role' => 'admin',
        ]));

        $response->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'new.customer@example.test']);
        $this->assertGuest();
    }

    public function test_worker_fields_are_required_for_workers(): void
    {
        $this->seed(TradeSeeder::class);

        $response = $this->from('/register')->post('/register', $this->customerPayload([
            'role' => 'worker',
        ]));

        $response->assertSessionHasErrors([
            'trade_id',
            'experience_years',
            'service_area',
            'bio',
        ]);
        $this->assertGuest();
    }

    public function test_email_and_phone_must_be_unique(): void
    {
        User::factory()->create([
            'email' => 'taken@example.test',
            'phone' => '+224622000088',
        ]);

        $response = $this->from('/register')->post('/register', $this->customerPayload([
            'email' => 'taken@example.test',
            'phone' => '+224622000088',
        ]));

        $response->assertSessionHasErrors(['email', 'phone']);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_terms_must_be_accepted(): void
    {
        $payload = $this->customerPayload();
        unset($payload['terms']);

        $response = $this->from('/register')->post('/register', $payload);

        $response->assertSessionHasErrors('terms');
        $this->assertGuest();
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function customerPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'New Customer',
            'email' => 'new.customer@example.test',
            'phone' => '+224621000099',
            'city' => 'Kaloum',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
            'terms' => '1',
        ], $overrides);
    }
}
