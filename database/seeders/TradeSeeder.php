<?php

namespace Database\Seeders;

use App\Models\Trade;
use Illuminate\Database\Seeder;

class TradeSeeder extends Seeder
{
    public function run(): void
    {
        $trades = [
            ['slug' => 'electrician', 'name_fr' => 'Électricien', 'name_en' => 'Electrician', 'icon' => 'bolt'],
            ['slug' => 'plumber', 'name_fr' => 'Plombier', 'name_en' => 'Plumber', 'icon' => 'droplet'],
            ['slug' => 'mason', 'name_fr' => 'Maçon', 'name_en' => 'Mason', 'icon' => 'bricks'],
            ['slug' => 'mechanic', 'name_fr' => 'Mécanicien', 'name_en' => 'Mechanic', 'icon' => 'car'],
            ['slug' => 'carpenter', 'name_fr' => 'Menuisier', 'name_en' => 'Carpenter', 'icon' => 'hammer'],
            ['slug' => 'painter', 'name_fr' => 'Peintre', 'name_en' => 'Painter', 'icon' => 'paint'],
        ];

        foreach ($trades as $trade) {
            Trade::updateOrCreate(
                ['slug' => $trade['slug']],
                $trade + ['is_active' => true]
            );
        }
    }
}
