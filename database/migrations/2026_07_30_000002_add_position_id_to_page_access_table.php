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
        Schema::table('page_access', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('position')->constrained('positions')->nullOnDelete();
        });

        // Backfill from the existing position string. page_access.position
        // was itself seeded from real Position values earlier, so this
        // should match cleanly, but stays nullable defensively — the
        // position string column is untouched either way.
        DB::statement('
            UPDATE page_access
            SET position_id = positions.id
            FROM positions
            WHERE page_access.position = positions.name
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('page_access', function (Blueprint $table) {
            $table->dropConstrainedForeignId('position_id');
        });
    }
};
