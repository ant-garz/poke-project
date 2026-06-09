<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pokemon_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('card_set_id')
                ->constrained('card_sets')
                ->cascadeOnDelete();

            $table->string('external_id')->unique(); // tcgdex id

            $table->string('name');

            $table->string('supertype')->nullable();
            $table->json('subtypes')->nullable();

            $table->string('rarity')->nullable();

            $table->string('number')->nullable();

            $table->text('image_url')->nullable();

            $table->unsignedSmallInteger('hp')->nullable();

            $table->json('raw_data')->nullable();

            $table->timestamps();

            $table->index(['pokemon_id', 'card_set_id']);
            $table->index('rarity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};