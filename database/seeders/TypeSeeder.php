<?php

namespace Database\Seeders;

use App\Models\Type;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Normal', 'color' => '#A8A77A'],
            ['name' => 'Fire', 'color' => '#EE8130'],
            ['name' => 'Water', 'color' => '#6390F0'],
            ['name' => 'Electric', 'color' => '#F7D02C'],
            ['name' => 'Grass', 'color' => '#7AC74C'],
            ['name' => 'Ice', 'color' => '#96D9D6'],
            ['name' => 'Fighting', 'color' => '#C22E28'],
            ['name' => 'Poison', 'color' => '#A33EA1'],
            ['name' => 'Ground', 'color' => '#E2BF65'],
            ['name' => 'Flying', 'color' => '#A98FF3'],
            ['name' => 'Psychic', 'color' => '#F95587'],
            ['name' => 'Bug', 'color' => '#A6B91A'],
            ['name' => 'Rock', 'color' => '#B6A136'],
            ['name' => 'Ghost', 'color' => '#735797'],
            ['name' => 'Dragon', 'color' => '#6F35FC'],
            ['name' => 'Dark', 'color' => '#705746'],
            ['name' => 'Steel', 'color' => '#B7B7CE'],
            ['name' => 'Fairy', 'color' => '#D685AD'],
        ];

        foreach ($types as $type) {
            Type::create([
                'name' => $type['name'],
                'slug' => Str::slug($type['name']),
                'color' => $type['color'],
                'text_color' => '#ffffff',
            ]);
        }
    }
}