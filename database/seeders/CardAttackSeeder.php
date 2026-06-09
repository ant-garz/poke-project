<?php

namespace Database\Seeders;

use App\Models\Card;
use App\Models\CardAttack;
use Illuminate\Database\Seeder;

class CardAttackSeeder extends Seeder
{
    public function run(): void
    {
        if (Card::count() === 0) {
            $this->command->warn('No Cards found. Skipping CardAttackSeeder.');
            return;
        }

        Card::all()->each(function ($card) {

            $attackCount = rand(1, 2);

            CardAttack::factory()
                ->count($attackCount)
                ->make()
                ->each(function ($attack) use ($card) {
                    $attack->card_id = $card->id;
                    $attack->save();
                });
        });
    }
}