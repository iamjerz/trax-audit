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
        Schema::table('users', function (Blueprint $table) {
            $table->string('supervisor_email')->nullable()->after('supervisor_id');
            $table->string('second_supervisor_id')->nullable()->after('supervisor_email');
            $table->string('second_supervisor_email')->nullable()->after('second_supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['supervisor_email', 'second_supervisor_id', 'second_supervisor_email']);
        });
    }
};
