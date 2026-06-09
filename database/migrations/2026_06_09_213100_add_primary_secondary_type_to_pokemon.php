<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * 1. Add new columns
         */
        Schema::table('pokemon', function (Blueprint $table) {
            $table->foreignId('primary_type_id')
                ->nullable()
                ->constrained('types')
                ->nullOnDelete()
                ->after('id');

            $table->foreignId('secondary_type_id')
                ->nullable()
                ->constrained('types')
                ->nullOnDelete()
                ->after('primary_type_id');
        });

        /**
         * 2. Backfill from pivot table
         */
        $rows = DB::table('pokemon_type')->get();

        $grouped = $rows->groupBy('pokemon_id');

        foreach ($grouped as $pokemonId => $types) {
            $primary = $types->values()->get(0)->type_id ?? null;
            $secondary = $types->values()->get(1)->type_id ?? null;

            DB::table('pokemon')
                ->where('id', $pokemonId)
                ->update([
                    'primary_type_id' => $primary,
                    'secondary_type_id' => $secondary,
                ]);
        }

        /**
         * 3. Drop pivot table (after migration)
         */
        Schema::dropIfExists('pokemon_type');
    }

    public function down(): void
    {
        /**
         * 1. Recreate pivot table
         */
        Schema::create('pokemon_type', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pokemon_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('slot')->nullable();

            $table->timestamps();

            $table->unique(['pokemon_id', 'type_id']);
        });

        /**
         * 2. Rehydrate pivot data from new columns
         */
        $rows = DB::table('pokemon')
            ->select('id', 'primary_type_id', 'secondary_type_id')
            ->get();

        foreach ($rows as $pokemon) {
            if ($pokemon->primary_type_id) {
                DB::table('pokemon_type')->insert([
                    'pokemon_id' => $pokemon->id,
                    'type_id' => $pokemon->primary_type_id,
                    'slot' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($pokemon->secondary_type_id) {
                DB::table('pokemon_type')->insert([
                    'pokemon_id' => $pokemon->id,
                    'type_id' => $pokemon->secondary_type_id,
                    'slot' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        /**
         * 3. Drop new columns
         */
        Schema::table('pokemon', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_type_id');
            $table->dropConstrainedForeignId('secondary_type_id');
        });
    }
};