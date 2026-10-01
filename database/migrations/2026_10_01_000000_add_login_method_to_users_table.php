<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // 'password' | 'microsoft' | 'both' — which sign-in method(s) this
            // account is allowed to use (see LoginController::authenticate /
            // handleMicrosoftCallback). Plain string, not a native enum —
            // matches how 'status' is already done, validated at the
            // application layer instead (Postgres in prod, SQLite in tests).
            $table->string('login_method')->default('both')->after('status');
        });

        // Backfill existing accounts to 'microsoft' — the company is pushing
        // everyone onto SSO going forward, not just new hires — except
        // admins, who stay on 'both' so there's always a break-glass way in
        // if Entra/Azure ever has an outage or this app registration gets
        // misconfigured again (admin-ness isn't a users column — it's an
        // extension_access row with access_type = 'admin', same convention
        // AppServiceProvider's page-access composer already uses).
        DB::table('users')->update(['login_method' => 'microsoft']);

        $adminEmployeeIds = DB::table('extension_access')
            ->where('access_type', 'admin')
            ->pluck('employeeid');

        if ($adminEmployeeIds->isNotEmpty()) {
            DB::table('users')
                ->whereIn('employeeid', $adminEmployeeIds)
                ->update(['login_method' => 'both']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('login_method');
        });
    }
};
