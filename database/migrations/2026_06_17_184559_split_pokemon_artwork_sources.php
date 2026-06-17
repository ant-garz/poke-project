<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pokemon', function (Blueprint $table) {

            $table->text('pokeapi_artwork_url')
                ->nullable()
                ->after('sprite_url');

            $table->text('tcgdex_artwork_base_url')
                ->nullable()
                ->after('pokeapi_artwork_url');

        });

        // Backfill existing data safely
        DB::table('pokemon')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                DB::table('pokemon')
                    ->where('id', $row->id)
                    ->update([
                        'pokeapi_artwork_url' => $row->artwork_url,
                        'tcgdex_artwork_base_url' => null,
                    ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->dropColumn([
                'pokeapi_artwork_url',
                'tcgdex_artwork_base_url',
            ]);
        });
    }
};