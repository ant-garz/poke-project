<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_attacks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('card_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');

            $table->unsignedSmallInteger('damage')->nullable();

            $table->text('effect')->nullable();

            $table->json('cost')->nullable();

            $table->timestamps();

            $table->index('card_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_attacks');
    }
};