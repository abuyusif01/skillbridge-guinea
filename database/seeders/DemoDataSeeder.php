<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\ReviewStatus;
use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Review;
use App\Models\Trade;
use App\Models\User;
use App\Models\WorkerProfile;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->user('Kader Fofana', 'admin@skillbridge.test', '+224620000001', UserRole::Admin, 'Conakry');
        $amadou = $this->user('Amadou Bah', 'amadou@example.test', '+224621000001', UserRole::Customer, 'Kaloum');
        $fatoumata = $this->user('Fatoumata Kaba', 'fatoumata@example.test', '+224622000002', UserRole::Customer, 'Matoto');

        $mamadou = $this->user('Mamadou Diallo', 'mamadou@example.test', '+224623000003', UserRole::Worker, 'Kaloum');
        $ibrahima = $this->user('Ibrahima Sow', 'ibrahima@example.test', '+224624000004', UserRole::Worker, 'Ratoma');
        $sekou = $this->user('Sekou Camara', 'sekou@example.test', '+224625000005', UserRole::Worker, 'Matoto');
        $kadiatou = $this->user('Kadiatou Bah', 'kadiatou@example.test', '+224626000006', UserRole::Worker, 'Kaloum');

        $electrician = Trade::where('slug', 'electrician')->firstOrFail();
        $plumber = Trade::where('slug', 'plumber')->firstOrFail();

        $this->profile($mamadou, $electrician, 'Électricien basé à Kaloum avec plus de 8 ans d’expérience dans les installations résidentielles et les petits commerces.', 8, 'Kaloum, Matoto, Matam', 150000);
        $this->profile($ibrahima, $electrician, 'Électricien à Ratoma, installations et dépannages rapides.', 5, 'Ratoma, Conakry', 120000);
        $this->profile($sekou, $electrician, 'Électricien à Matoto, installations et dépannages rapides.', 6, 'Matoto, Conakry', 100000);
        $this->profile($kadiatou, $plumber, 'Plombière à Kaloum, réparations et installations sanitaires.', 4, 'Kaloum, Conakry', 130000);

        Booking::firstOrCreate([
            'customer_id' => $amadou->id,
            'worker_id' => $mamadou->id,
            'description' => 'Réparer une prise électrique qui ne fonctionne plus dans le salon.',
        ],
            [
                'trade_id' => $electrician->id,
                'preferred_date' => now()->addDays(3)->toDateString(),
                'time_slot' => 'morning',
                'address' => 'Kaloum, rue KA-020',
                'contact_phone' => $amadou->phone,
                'status' => BookingStatus::Pending,
            ]
        );

        Booking::firstOrCreate(
            [
                'customer_id' => $fatoumata->id,
                'worker_id' => $mamadou->id,
                'description' => 'Installation de 3 lampes dans une chambre et le couloir.',
            ],
            [
                'trade_id' => $electrician->id,
                'preferred_date' => now()->addDays(5)->toDateString(),
                'time_slot' => 'afternoon',
                'address' => 'Matoto, quartier Dabompa',
                'contact_phone' => $fatoumata->phone,
                'status' => BookingStatus::Pending,
            ]
        );

        Booking::firstOrCreate(
            [
                'customer_id' => $amadou->id,
                'worker_id' => $ibrahima->id,
                'description' => 'Installation d’un ventilateur de plafond.',
            ],
            [
                'trade_id' => $electrician->id,
                'preferred_date' => now()->addDays(2)->toDateString(),
                'time_slot' => 'morning',
                'address' => 'Ratoma, Nongo',
                'contact_phone' => $amadou->phone,
                'status' => BookingStatus::Accepted,
                'responded_at' => now(),
            ]
        );

        $completed = Booking::firstOrCreate(
            [
                'customer_id' => $fatoumata->id,
                'worker_id' => $sekou->id,
                'description' => 'Réparation de deux prises et remplacement d’un interrupteur.',
            ],
            [
                'trade_id' => $electrician->id,
                'preferred_date' => now()->subDays(4)->toDateString(),
                'time_slot' => 'afternoon',
                'address' => 'Matoto, quartier Dabompa',
                'contact_phone' => $fatoumata->phone,
                'status' => BookingStatus::Completed,
                'responded_at' => now()->subDays(5),
                'completed_at' => now()->subDays(4),
            ]
        );

        Review::updateOrCreate(
            ['booking_id' => $completed->id],
            [
                'customer_id' => $fatoumata->id,
                'worker_id' => $sekou->id,
                'rating' => 5,
                'comment' => 'Excellent travail, très rapide et professionnel.',
                'status' => ReviewStatus::Published,
            ]
        );

        $mamadouCompleted = Booking::firstOrCreate(
            [
                'customer_id' => $fatoumata->id,
                'worker_id' => $mamadou->id,
                'description' => 'Réparation du tableau électrique et remplacement de deux disjoncteurs.',
            ],
            [
                'trade_id' => $electrician->id,
                'preferred_date' => now()->subDays(8)->toDateString(),
                'time_slot' => 'morning',
                'address' => 'Matoto, quartier Dabompa',
                'contact_phone' => $fatoumata->phone,
                'status' => BookingStatus::Completed,
                'responded_at' => now()->subDays(9),
                'completed_at' => now()->subDays(8),
            ]
        );

        Review::updateOrCreate(
            ['booking_id' => $mamadouCompleted->id],
            [
                'customer_id' => $fatoumata->id,
                'worker_id' => $mamadou->id,
                'rating' => 5,
                'comment' => 'Professionnel et ponctuel. Je recommande.',
                'status' => ReviewStatus::Published,
            ]
        );

        Booking::firstOrCreate(
            [
                'customer_id' => $amadou->id,
                'worker_id' => $mamadou->id,
                'description' => 'Installation d’un ventilateur de plafond dans la chambre.',
            ],
            [
                'trade_id' => $electrician->id,
                'preferred_date' => now()->addDays(1)->toDateString(),
                'time_slot' => 'evening',
                'address' => 'Kaloum, rue KA-020',
                'contact_phone' => $amadou->phone,
                'status' => BookingStatus::Accepted,
                'responded_at' => now(),
            ]
        );

        Booking::firstOrCreate(
            [
                'customer_id' => $amadou->id,
                'worker_id' => $mamadou->id,
                'description' => 'Installation de deux prises et d’un interrupteur dans la cuisine.',
            ],
            [
                'trade_id' => $electrician->id,
                'preferred_date' => now()->subDays(2)->toDateString(),
                'time_slot' => 'afternoon',
                'address' => 'Kaloum, rue KA-020',
                'contact_phone' => $amadou->phone,
                'status' => BookingStatus::Completed,
                'responded_at' => now()->subDays(3),
                'completed_at' => now()->subDays(2),
            ]
        );

    }

    private function user(string $name, string $email, string $phone, UserRole $role, string $city): User
    {
        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'phone' => $phone,
                'password' => 'password',
                'city' => $city,
                'locale' => 'en',
            ]
        );

        $user->forceFill([
            'role' => $role,
            'is_active' => true,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ])->save();

        return $user;
    }

    private function profile(
        User $worker,
        Trade $trade,
        string $bio,
        int $experience,
        string $serviceArea,
        int $rateMin
    ): WorkerProfile {
        return WorkerProfile::updateOrCreate(
            ['user_id' => $worker->id],
            [
                'trade_id' => $trade->id,
                'bio' => $bio,
                'experience_years' => $experience,
                'service_area' => $serviceArea,
                'rate_min' => $rateMin,
                'is_available' => true,
                'verified_at' => now(),
            ]
        );
    }
}
