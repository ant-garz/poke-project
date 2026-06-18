<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->enum('tcgdex_sync_status', [
                            'idle',
                            'queued',
                            'processing',
                            'completed',
                            'failed',
                        ])
                        ->default('idle')
                        ->index();

            $table->timestamp('tcg_sync_started_at')
                ->nullable();

            $table->timestamp('source_tcgdex_synced_at')
                ->nullable()
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('pokemon', function (Blueprint $table) {
            $table->dropColumn([
                'tcg_sync_status',
                'tcg_sync_started_at',
            ]);
        });
    }
};