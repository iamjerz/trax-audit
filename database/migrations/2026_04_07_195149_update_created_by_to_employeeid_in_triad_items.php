<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('triad_items', function (Blueprint $table) {
            // ❗ Drop old foreign key first
            $table->dropForeign(['created_by']);

            // ❗ Change column type (bigint → string)
            $table->string('created_by')->change();

            // ✅ Add new foreign key (employeeid)
            $table->foreign('created_by')
                  ->references('employeeid')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('triad_items', function (Blueprint $table) {
            // rollback if needed

            $table->dropForeign(['created_by']);

            $table->unsignedBigInteger('created_by')->change();

            $table->foreign('created_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }
};