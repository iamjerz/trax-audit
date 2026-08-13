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
        Schema::table('user_input_audits', function (Blueprint $table) {
            $table->string('client_code')->nullable()->after('carrier_name');
            $table->boolean('is_calibration')->default(false)->after('exception_owner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_input_audits', function (Blueprint $table) {
            $table->dropColumn(['client_code', 'is_calibration']);
        });
    }
};
