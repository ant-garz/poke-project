<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('card_attacks', function (Blueprint $table) {
            $table->string('damage')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('card_attacks', function (Blueprint $table) {
            $table->integer('damage')->nullable()->change();
        });
    }
};