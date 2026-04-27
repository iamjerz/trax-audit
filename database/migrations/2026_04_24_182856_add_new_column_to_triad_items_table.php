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
        Schema::table('triad_items', function (Blueprint $table) {
              $table->string('reference_id')->nullable(); // 👈 add your column here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('triad_items', function (Blueprint $table) {
            //
        });
    }
};
