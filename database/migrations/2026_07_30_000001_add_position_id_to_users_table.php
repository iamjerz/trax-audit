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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('position_id')->nullable()->after('position')->constrained('positions')->nullOnDelete();
        });

        // Backfill from the existing position string. Left nullable on
        // purpose: if any user's position doesn't match a positions.name
        // exactly (whitespace/casing drift), position_id just stays null
        // here rather than failing the migration — the position string
        // column is untouched either way, so nothing about existing
        // behavior changes. Worth running
        // `select employeeid, position from users where position_id is null`
        // after this to see if anyone needs manual cleanup.
        DB::statement('
            UPDATE users
            SET position_id = positions.id
            FROM positions
            WHERE users.position = positions.name
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('position_id');
        });
    }
};
