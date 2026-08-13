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
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('scope')->default('all'); // 'own' | 'team' | 'all'
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Canonical list frozen from the two hardcoded dropdowns that existed
        // before this migration — resources/views/sub/edituser.blade.php and
        // resources/views/users.blade.php's Add New User modal. The latter
        // had 5 titles (Chief *) the former didn't, which is exactly the kind
        // of drift this table exists to prevent going forward.
        //
        // scope mirrors the level-restriction logic already live in
        // MonitoringTicket / ReconTiketController / CoachingTicket / TriadTicket:
        // LDA -> own, any "*Supervisor" title -> team, everything else -> all.
        $now = now();

        $positions = [
            ['name' => 'user',                    'scope' => 'all',  'sort_order' => 0],
            ['name' => 'Audit Supervisor',         'scope' => 'team', 'sort_order' => 1],
            ['name' => 'Vendor Manager',           'scope' => 'all',  'sort_order' => 2],
            ['name' => 'Duplicate',                'scope' => 'all',  'sort_order' => 3],
            ['name' => 'LDA',                      'scope' => 'own',  'sort_order' => 4],
            ['name' => 'Duplicate Manager',        'scope' => 'all',  'sort_order' => 5],
            ['name' => 'GSS Supervisor',           'scope' => 'team', 'sort_order' => 6],
            ['name' => 'Audit Manager',            'scope' => 'all',  'sort_order' => 7],
            ['name' => 'VP, Audit',                'scope' => 'all',  'sort_order' => 8],
            ['name' => 'Rate Loading Supervisor',  'scope' => 'team', 'sort_order' => 9],
            ['name' => 'Post Audit Supervisor',    'scope' => 'team', 'sort_order' => 10],
            ['name' => 'Audit Sr. Manager',        'scope' => 'all',  'sort_order' => 11],
            ['name' => 'SME',                      'scope' => 'all',  'sort_order' => 12],
            ['name' => 'GSS',                      'scope' => 'all',  'sort_order' => 13],
            ['name' => 'Post Audit',               'scope' => 'all',  'sort_order' => 14],
            ['name' => 'GSS Manager',               'scope' => 'all',  'sort_order' => 15],
            ['name' => 'AI Prompting Engineer',    'scope' => 'all',  'sort_order' => 16],
            ['name' => 'Rate Loading Analyst',     'scope' => 'all',  'sort_order' => 17],
            ['name' => 'Ops Analytics Manager',    'scope' => 'all',  'sort_order' => 18],
            ['name' => 'Service',                  'scope' => 'all',  'sort_order' => 19],
            ['name' => 'Chief Operating Officer',  'scope' => 'all',  'sort_order' => 20],
            ['name' => 'Chief Executive Officer',  'scope' => 'all',  'sort_order' => 21],
            ['name' => 'Chief Financial Officer',  'scope' => 'all',  'sort_order' => 22],
            ['name' => 'Chief Technology Officer', 'scope' => 'all',  'sort_order' => 23],
            ['name' => 'Chief Product Officer',    'scope' => 'all',  'sort_order' => 24],
        ];

        foreach ($positions as $position) {
            $position['created_at'] = $now;
            $position['updated_at'] = $now;
            DB::table('positions')->insertOrIgnore($position);
        }

        // Defensive backfill: any distinct position value that already exists
        // on real users but isn't in the canonical list above (a legacy typo,
        // or a value entered before either dropdown was updated) gets its own
        // row with the permissive 'all' scope, so nobody's access silently
        // breaks the moment this ships.
        $knownNames = DB::table('positions')->pluck('name')->all();

        $strayPositions = DB::table('users')
            ->whereNotNull('position')
            ->where('position', '!=', '')
            ->whereNotIn('position', $knownNames)
            ->distinct()
            ->pluck('position');

        foreach ($strayPositions as $stray) {
            DB::table('positions')->insertOrIgnore([
                'name'       => $stray,
                'scope'      => 'all',
                'sort_order' => 999,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
