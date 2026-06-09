<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->json('raw_pokeapi')->nullable();
            $table->json('raw_tcgdex')->nullable();
            $table->boolean('is_enriched')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->dropColumn([
                'raw_pokeapi',
                'raw_tcgdex',
                'is_enriched',
            ]);
        });
    }
};