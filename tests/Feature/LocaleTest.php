<?php

namespace Tests\Feature;

use App\Models\Trade;
use App\Models\User;
use App\Models\WorkerProfile;
use Database\Seeders\TradeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TradeSeeder::class);
    }

    public function test_english_is_the_default_locale(): void
    {
        $this->worker();

        $this->get('/search')
            ->assertOk()
            ->assertSee('Electrician')
            ->assertDontSee('Électricien');
    }

    public function test_the_locale_cookie_switches_dynamic_labels_to_french(): void
    {
        $this->worker();

        $this->withUnencryptedCookie('skillbridge_locale', 'fr')
            ->get('/search')
            ->assertOk()
            ->assertSee('Électricien');
    }

    public function test_an_unknown_locale_cookie_falls_back_to_english(): void
    {
        $this->worker();

        $this->withUnencryptedCookie('skillbridge_locale', 'de')
            ->get('/search')
            ->assertOk()
            ->assertSee('Electrician');
    }

    private function worker(): User
    {
        $worker = User::factory()->worker()->create(['city' => 'Kaloum']);
        $trade = Trade::where('slug', 'electrician')->firstOrFail();

        WorkerProfile::factory()->create([
            'user_id' => $worker->id,
            'trade_id' => $trade->id,
            'service_area' => 'Kaloum',
        ]);

        return $worker->refresh();
    }
}
