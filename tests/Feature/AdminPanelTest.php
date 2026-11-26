<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Review;
use App\Models\Trade;
use App\Models\User;
use App\Models\WorkerProfile;
use Database\Seeders\TradeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_can_access_the_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('Administration');
    }

    public function test_customers_cannot_access_the_admin_dashboard(): void
    {
        $customer = User::factory()->create();

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_admins_can_suspend_and_reactivate_users(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->create();

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $target), ['is_active' => 0])
            ->assertRedirect();

        $this->assertFalse($target->refresh()->is_active);

        $this->actingAs($admin)
            ->patch(route('admin.users.status', $target), ['is_active' => 1])
            ->assertRedirect();

        $this->assertTrue($target->refresh()->is_active);
    }

    public function test_admins_cannot_suspend_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->patch(route('admin.users.status', $admin), ['is_active' => 0])
            ->assertSessionHasErrors('is_active');

        $this->assertTrue($admin->refresh()->is_active);
    }

    public function test_admins_can_delete_reviews(): void
    {
        $this->seed(TradeSeeder::class);

        $admin = User::factory()->admin()->create();
        $customer = User::factory()->create();
        $worker = User::factory()->worker()->create();
        $trade = Trade::where('slug', 'electrician')->firstOrFail();

        WorkerProfile::factory()->create([
            'user_id' => $worker->id,
            'trade_id' => $trade->id,
        ]);

        $booking = Booking::factory()->completed()->create([
            'customer_id' => $customer->id,
            'worker_id' => $worker->id,
            'trade_id' => $trade->id,
        ]);

        $review = Review::factory()->create([
            'booking_id' => $booking->id,
            'rating' => 3,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.reviews.destroy', $review))
            ->assertRedirect();

        $this->assertDatabaseMissing('reviews', ['id' => $review->id]);
    }
}
