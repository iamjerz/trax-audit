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
        Schema::create('carrier_codes', function (Blueprint $table) {
            $table->id(); // auto increment ID
            $table->string('name');
            $table->string('added_by');
            $table->timestamp('timestamp')->useCurrent(); // auto current time
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carrier_codes');
    }
};
