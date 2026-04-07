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
        Schema::create('user_input_audits', function (Blueprint $table) {
            $table->id();
            $table->string('audit_id')->unique();
            $table->string('lda_id');
            $table->date('audit_date_1');
            $table->string('audit_sup_name');
            $table->string('auditors_name');
            $table->date('audit_date_2')->nullable();
            $table->string('invoice_id');
            $table->string('carrier_name');
            $table->string('exception_status');
            $table->string('exception_owner');
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_input_audits');
    }
};
