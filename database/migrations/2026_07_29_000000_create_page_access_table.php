<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Support\AccessRoles;
use App\Support\PageRegistry;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('page_access', function (Blueprint $table) {
            $table->id();
            $table->string('page_key');
            $table->string('position');
            $table->string('created_by');
            $table->timestamps();
            $table->unique(['page_key', 'position']);
        });

        $this->seedFromCurrentAccess();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_access');
    }

    /**
     * Preserve today's effective web-page access at cutover: for every
     * Position, if any user in that position currently satisfies a page's
     * old capability requirement (access:web_dashboard, access:web_managers,
     * etc — see PageRegistry::$legacyRequirements), grant that Position the
     * equivalent page here. Admins aren't seeded — they already bypass the
     * new `page:` middleware at runtime regardless of Position.
     */
    private function seedFromCurrentAccess(): void
    {
        $rawByPosition = DB::table('users')
            ->join('extension_access', 'users.employeeid', '=', 'extension_access.employeeid')
            ->whereNotNull('users.position')
            ->where('users.position', '!=', '')
            ->select('users.position', 'extension_access.access_type')
            ->get()
            ->groupBy('position')
            ->map(fn ($rows) => $rows->pluck('access_type')->unique()->values()->all());

        $now = now();
        $rows = [];

        foreach ($rawByPosition as $position => $rawTypes) {
            $expanded = AccessRoles::expand($rawTypes);

            foreach (PageRegistry::$legacyRequirements as $pageKey => $required) {
                if (count(array_intersect($required, $expanded)) > 0) {
                    $rows[] = [
                        'page_key'   => $pageKey,
                        'position'   => $position,
                        'created_by' => 'migration:seed',
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        if (! empty($rows)) {
            DB::table('page_access')->insert($rows);
        }
    }
};
