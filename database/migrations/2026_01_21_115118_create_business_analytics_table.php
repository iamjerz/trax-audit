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
        Schema::create('business_analytics', function (Blueprint $table) {
            $table->id();
            $table->string('audit_id');
            $table->string('sign_carrier');
            $table->string('follow_up');
            $table->string('many_days');
            $table->string('cause_issue');
            $table->string('impact_area');
            $table->string('impact_factor');
            $table->string('accountable_factors');
            $table->string('root_cause');
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_analytics');
    }
};
