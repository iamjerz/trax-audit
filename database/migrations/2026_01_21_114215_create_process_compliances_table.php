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
        Schema::create('process_compliances', function (Blueprint $table) {
            $table->id();
            $table->string('audit_id');
            $table->string('pc_comment_1');
            $table->string('pc_outcome_1');
            $table->string('pc_comment_2');
            $table->string('pc_outcome_2');
            $table->string('pc_comment_3');
            $table->string('pc_outcome_3');
            $table->string('pc_comment_4');
            $table->string('pc_outcome_4');
            $table->string('total_score');
            $table->string('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_compliances');
    }
};
