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
        Schema::table('recon_item_comments', function (Blueprint $table) {
            //
            $table->string('employeeid')->nullable()->after('comments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recon_item_comments', function (Blueprint $table) {
            //
            $table->dropColumn(['employeeid']);
        });
    }
};
