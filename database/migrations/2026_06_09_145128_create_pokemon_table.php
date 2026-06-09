<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokemon', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('pokedex_number')->unique();
            $table->string('name');
            $table->string('slug')->unique();

            // Variant system (lightweight Option B)
            $table->boolean('is_default')->default(true);
            $table->foreignId('base_pokemon_id')
                ->nullable()
                ->constrained('pokemon')
                ->nullOnDelete();

            // Base stats (CSV source)
            $table->unsignedSmallInteger('hp');
            $table->unsignedSmallInteger('attack');
            $table->unsignedSmallInteger('defense');
            $table->unsignedSmallInteger('special_attack');
            $table->unsignedSmallInteger('special_defense');
            $table->unsignedSmallInteger('speed');

            // PokeAPI enrichment
            $table->unsignedSmallInteger('height')->nullable();
            $table->unsignedSmallInteger('weight')->nullable();
            $table->unsignedSmallInteger('base_experience')->nullable();

            $table->text('description')->nullable();

            // Media
            $table->text('sprite_url')->nullable();
            $table->text('artwork_url')->nullable();
            $table->text('cry_url')->nullable();

            // Sync tracking
            $table->timestamp('source_csv_imported_at')->nullable();
            $table->timestamp('source_pokeapi_synced_at')->nullable();
            $table->timestamp('source_tcgdex_synced_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('name');
            $table->index('base_pokemon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon');
    }
};