<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('form_list', function (Blueprint $table) {
            // 🔴 DROP foreign key FIRST
            $table->dropForeign('form_list_created_by_foreign');

            // 🔁 CHANGE column type
            $table->string('created_by')->change();
        });
    }

    public function down(): void
    {
        Schema::table('form_list', function (Blueprint $table) {
            // revert back to integer
            $table->integer('created_by')->change();

            // re-add foreign key
            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }
};