<?php

namespace Tests\Feature;

use App\Models\Trade;
use App\Models\User;
use App\Models\WorkerProfile;
use Database\Seeders\TradeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TradeSeeder::class);
    }

    public function test_worker_can_view_the_profile_edit_form(): void
    {
        $worker = $this->worker();

        $this->actingAs($worker)
            ->get(route('worker.profile.edit'))
            ->assertOk()
            ->assertSee('Modifier mon profil')
            ->assertSee('Electrician')
            ->assertSee($worker->workerProfile->bio);
    }

    public function test_worker_can_update_their_profile(): void
    {
        $worker = $this->worker();
        $plumber = Trade::where('slug', 'plumber')->firstOrFail();

        $response = $this->actingAs($worker)->patch(route('worker.profile.update'), [
            'trade_id' => $plumber->id,
            'bio' => 'Plomberie, dépannage et installation de sanitaires.',
            'experience_years' => 9,
            'service_area' => 'Ratoma, Matoto',
            'rate_min' => 175000,
            'is_available' => '1',
        ]);

        $response->assertRedirect(route('worker.profile.edit'));
        $response->assertSessionHas('status');

        $profile = $worker->workerProfile->fresh();

        $this->assertSame($plumber->id, $profile->trade_id);
        $this->assertSame('Plomberie, dépannage et installation de sanitaires.', $profile->bio);
        $this->assertSame(9, $profile->experience_years);
        $this->assertSame('Ratoma, Matoto', $profile->service_area);
        $this->assertSame(175000, $profile->rate_min);
        $this->assertTrue($profile->is_available);
    }

    public function test_worker_can_mark_themselves_unavailable(): void
    {
        $worker = $this->worker();

        $this->actingAs($worker)->patch(route('worker.profile.update'), [
            'trade_id' => $worker->workerProfile->trade_id,
            'bio' => $worker->workerProfile->bio,
            'experience_years' => $worker->workerProfile->experience_years,
            'service_area' => $worker->workerProfile->service_area,
            'rate_min' => $worker->workerProfile->rate_min,
        ]);

        $this->assertFalse($worker->workerProfile->fresh()->is_available);

        $this->get(route('search'))->assertDontSee($worker->name);
    }

    public function test_profile_update_is_validated(): void
    {
        $worker = $this->worker();

        $response = $this->actingAs($worker)
            ->from(route('worker.profile.edit'))
            ->patch(route('worker.profile.update'), [
                'trade_id' => 99999,
                'bio' => '',
                'experience_years' => 61,
                'service_area' => '',
                'rate_min' => -100,
            ]);

        $response->assertRedirect(route('worker.profile.edit'));
        $response->assertSessionHasErrors([
            'trade_id',
            'bio',
            'experience_years',
            'service_area',
            'rate_min',
        ]);
    }

    public function test_a_worker_without_a_profile_gets_a_not_found_response(): void
    {
        $worker = User::factory()->worker()->create();

        $this->actingAs($worker)
            ->get(route('worker.profile.edit'))
            ->assertNotFound();
    }

    public function test_customers_and_admins_cannot_edit_worker_profiles(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($customer)->get(route('worker.profile.edit'))->assertForbidden();
        $this->actingAs($admin)->get(route('worker.profile.edit'))->assertForbidden();
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('worker.profile.edit'))->assertRedirect(route('login'));
    }

    private function worker(): User
    {
        $worker = User::factory()->worker()->create(['city' => 'Kaloum']);
        $trade = Trade::where('slug', 'electrician')->firstOrFail();

        WorkerProfile::factory()->create([
            'user_id' => $worker->id,
            'trade_id' => $trade->id,
            'bio' => 'Électricité générale et dépannage.',
            'service_area' => 'Kaloum',
            'is_available' => true,
        ]);

        return $worker->refresh();
    }
}
