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
            $table->boolean('is_recon_cancelled')->default(false)->after('invoice_status');
            $table->text('who_cancelled')->nullable()->after('is_recon_cancelled');
            $table->text('reason_cancelled')->nullable()->after('who_cancelled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recon_action_items', function (Blueprint $table) {
            $table->dropColumn(['is_recon_cancelled', 'who_cancelled', 'reason_cancelled']);
        });
    }
};
