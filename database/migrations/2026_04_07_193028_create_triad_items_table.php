<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('triad_items', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // 🔥 important
            $table->jsonb('triad');   
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // ✅ ADD INDEX HERE
        DB::statement('CREATE INDEX triad_items_triad_gin ON triad_items USING GIN (triad)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('triad_items');
    }
};
