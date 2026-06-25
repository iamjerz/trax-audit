<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Verifications
        Schema::table('verifications', function (Blueprint $table) {
            $table->text('ver_comment_1')->change();
            $table->text('ver_comment_2')->change();
        });

        // Process Compliances
        Schema::table('process_compliances', function (Blueprint $table) {
            $table->text('pc_comment_1')->change();
            $table->text('pc_comment_2')->change();
            $table->text('pc_comment_3')->change();
            $table->text('pc_comment_4')->change();
        });

        // Engagements
        Schema::table('engagements', function (Blueprint $table) {
            $table->text('eng_comment_1')->change();
            $table->text('eng_comment_2')->change();
            $table->text('eng_comment_3')->change();
            $table->text('eng_comment_4')->change();
        });

        // Business Analytics
        Schema::table('business_analytics', function (Blueprint $table) {
            $table->text('cause_issue')->change();
            $table->text('impact_area')->change();
            $table->text('impact_factor')->change();
            $table->text('accountable_factors')->change();
            $table->text('root_cause')->change();
        });
    }

    public function down(): void
    {
        // Verifications
        Schema::table('verifications', function (Blueprint $table) {
            $table->string('ver_comment_1')->change();
            $table->string('ver_comment_2')->change();
        });

        // Process Compliances
        Schema::table('process_compliances', function (Blueprint $table) {
            $table->string('pc_comment_1')->change();
            $table->string('pc_comment_2')->change();
            $table->string('pc_comment_3')->change();
            $table->string('pc_comment_4')->change();
        });

        // Engagements
        Schema::table('engagements', function (Blueprint $table) {
            $table->string('eng_comment_1')->change();
            $table->string('eng_comment_2')->change();
            $table->string('eng_comment_3')->change();
            $table->string('eng_comment_4')->change();
        });

        // Business Analytics
        Schema::table('business_analytics', function (Blueprint $table) {
            $table->string('cause_issue')->change();
            $table->string('impact_area')->change();
            $table->string('impact_factor')->change();
            $table->string('accountable_factors')->change();
            $table->string('root_cause')->change();
        });
    }
};