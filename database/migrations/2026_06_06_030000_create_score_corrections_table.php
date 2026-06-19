<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('score_corrections', function (Blueprint $table) {
            $table->id();
            $table->string('audit_id')->index();
            $table->unsignedBigInteger('dispute_id')->nullable();
            $table->string('changed_by');           // supervisor who made the correction
            $table->text('reason');
            $table->json('old_values');             // snapshot before
            $table->json('new_values');             // snapshot after
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('score_corrections');
    }
};
