<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pokemon_import_batches', function (Blueprint $table) {
            $table->id();

            // file tracking
            $table->string('original_filename');
            $table->string('file_path');

            // lifecycle state
            $table->enum('status', [
                'uploaded',
                'processing',
                'completed',
                'failed',
            ])->default('uploaded');

            // progress tracking
            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);

            // optional debugging / extensibility
            $table->json('meta')->nullable();

            $table->timestamps();

            // helpful indexes (important for polling UI)
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pokemon_import_batches');
    }
};