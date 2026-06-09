<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropForeign(['pokemon_id']);
            $table->dropColumn('pokemon_id');

            $table->nullableMorphs('cardable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {

                $table->dropMorphs('cardable');

                $table->foreignId('pokemon_id')
                    ->nullable()
                    ->constrained()
                    ->cascadeOnDelete();
            });
    }
};
