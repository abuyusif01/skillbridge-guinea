<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Review;
use App\Models\User;
use App\Models\WorkerProfile;
use Database\Seeders\TradeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_trade_seeder_creates_the_six_categories(): void
    {
        $this->seed(TradeSeeder::class);

        $this->assertDatabaseCount('trades', 6);
        $this->assertDatabaseHas('trades', [
            'slug' => 'electrician',
            'name_fr' => 'Électricien',
            'name_en' => 'Electrician',
        ]);
    }

    public function test_a_worker_profile_belongs_to_a_user_and_trade(): void
    {
        $profile = WorkerProfile::factory()->create();

        $this->assertInstanceOf(User::class, $profile->user);
        $this->assertTrue($profile->user->isWorker());
        $this->assertNotNull($profile->trade);
    }

    public function test_a_booking_casts_status_and_exposes_relationships(): void
    {
        $booking = Booking::factory()->accepted()->create();

        $this->assertSame(BookingStatus::Accepted, $booking->status);
        $this->assertInstanceOf(User::class, $booking->customer);
        $this->assertInstanceOf(User::class, $booking->worker);
        $this->assertNotNull($booking->trade);
    }

    public function test_a_review_casts_status_and_belongs_to_a_completed_booking(): void
    {
        $review = Review::factory()->create(['rating' => 5]);

        $this->assertSame(ReviewStatus::Published, $review->status);
        $this->assertSame(5, $review->rating);
        $this->assertTrue($review->booking->isCompleted());
        $this->assertSame($review->booking->customer_id, $review->customer_id);
        $this->assertSame($review->booking->worker_id, $review->worker_id);
    }

    public function test_user_roles_are_cast_to_enum(): void
    {
        $user = User::factory()->admin()->create();

        $this->assertSame(UserRole::Admin, $user->role);
        $this->assertTrue($user->isAdmin());
    }
}
