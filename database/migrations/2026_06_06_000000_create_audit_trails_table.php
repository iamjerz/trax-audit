<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();

            // Who performed the action (null for guests / failed logins / system)
            $table->string('employeeid')->nullable()->index();
            $table->string('actor_name')->nullable();

            // What happened
            $table->string('event')->index();          // login, logout, login_failed, created, updated, deleted, status_changed, ...
            $table->text('description')->nullable();

            // What it happened to
            $table->string('auditable_type')->nullable()->index(); // model class / table / 'auth'
            $table->string('auditable_id')->nullable()->index();   // target key

            // Before / after snapshots
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Request context
            $table->string('method', 10)->nullable();
            $table->text('url')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
