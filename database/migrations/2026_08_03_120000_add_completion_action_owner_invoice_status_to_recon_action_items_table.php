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
        Schema::table('recon_action_items', function (Blueprint $table) {
            $table->text('completion_date')->nullable()->after('assigned_to');
            $table->text('action_owner')->nullable()->after('completion_date');
            $table->text('invoice_status')->nullable()->after('action_owner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recon_action_items', function (Blueprint $table) {
            $table->dropColumn(['completion_date', 'action_owner', 'invoice_status']);
        });
    }
};
