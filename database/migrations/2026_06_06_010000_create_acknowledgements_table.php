<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acknowledgements', function (Blueprint $table) {
            $table->id();
            $table->string('reference_type')->default('audit'); // audit | coaching | triad
            $table->string('reference_id');                     // e.g. audit_id
            $table->string('employeeid');                       // who acknowledged
            $table->text('note')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acknowledgements');
    }
};
