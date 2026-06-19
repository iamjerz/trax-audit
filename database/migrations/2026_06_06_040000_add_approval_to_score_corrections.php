<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('score_corrections', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('reason'); // pending | approved | rejected
            $table->string('approved_by')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('decision_note')->nullable()->after('approved_at');
        });

        // Any corrections recorded before this feature were applied immediately,
        // so treat them as already approved.
        DB::table('score_corrections')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('score_corrections', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_by', 'approved_at', 'decision_note']);
        });
    }
};
