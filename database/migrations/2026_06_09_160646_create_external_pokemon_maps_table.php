<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('external_pokemon_maps', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pokemon_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('source'); 
            // pokeapi | tcgdex

            $table->string('external_id')->nullable();
            $table->string('external_name')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['source', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_pokemon_maps');
    }
};