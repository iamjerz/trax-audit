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
        Schema::create('recon_action_items', function (Blueprint $table) {

            $table->id(); // bigint auto increment

            $table->text('submission_id')->nullable()->unique();
            $table->date('recon_call_date')->nullable();
            $table->text('lda_email')->nullable();
            $table->text('audit_sup_email')->nullable();
            $table->text('client_code')->nullable();
            $table->text('carrier_code')->nullable();
            $table->text('region')->nullable();

            $table->text('action_item_summary')->nullable();
            $table->text('action_item_details')->nullable();
            $table->text('jira_ticket')->nullable();
            $table->text('status')->nullable();

            $table->json('raw_data')->nullable(); // jsonb equivalent

            $table->timestampTz('created_at')->nullable()->useCurrent();

            // NOTE: Laravel usually adds updated_at, but your schema doesn't have it
            // so we skip $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recon_action_items');
    }
};
