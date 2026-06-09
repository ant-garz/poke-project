<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pokemon_type', function (Blueprint $table) {
            $table->foreignId('pokemon_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedTinyInteger('slot'); // 1 or 2

            $table->timestamps();

            // prevent duplicates
            $table->unique(['pokemon_id', 'type_id']);

            // ensure ordering is unique per pokemon
            $table->unique(['pokemon_id', 'slot']);

            $table->index(['type_id', 'pokemon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon_type');
    }
};