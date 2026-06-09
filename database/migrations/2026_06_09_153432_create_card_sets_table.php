<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_sets', function (Blueprint $table) {
            $table->id();

            $table->string('external_id')->unique(); // tcgdex set id

            $table->string('name');
            $table->string('series')->nullable();

            $table->text('logo_url')->nullable();
            $table->text('symbol_url')->nullable();

            $table->date('release_date')->nullable();

            $table->unsignedInteger('card_count_official')->nullable();
            $table->unsignedInteger('card_count_total')->nullable();

            $table->json('raw_data')->nullable();

            $table->timestamps();

            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_sets');
    }
};