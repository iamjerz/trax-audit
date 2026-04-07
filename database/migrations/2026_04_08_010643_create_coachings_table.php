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
        Schema::create('coachings', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->index();

            $table->jsonb('smart'); // ✅ SMART block
            $table->jsonb('grow');  // ✅ GROW block

            $table->string('created_by');

            $table->timestamps();
        });

        DB::statement('CREATE INDEX coaching_smart_gin ON coachings USING GIN (smart)');
        DB::statement('CREATE INDEX coaching_grow_gin ON coachings USING GIN (grow)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coachings');
    }
};
