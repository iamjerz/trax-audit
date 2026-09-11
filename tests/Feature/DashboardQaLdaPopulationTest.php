<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\DashboardControllerMain;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Boundary-case coverage for the "Total LDA" / "Audited LDAs" /
 * "LDAs With No Audits" logic in DashboardControllerMain::dashbaordCard(),
 * per the agreed business rule:
 *
 *   - Total LDA = the expected LDA population for the selected reporting
 *     period (users.status/effectivity_date_leaver), NOT a count of who has
 *     audit rows.
 *   - Start Date is EXCLUSIVE for the Leaver Date: leaver_date == date_from
 *     excludes that person (as of day 1 of the range they'd already left).
 *   - End Date does NOT constrain population membership: a leaver still
 *     counts even when date_to lands on, or long after, their Leaver Date —
 *     only whether they were present as of the START of the range matters.
 *   - No Leaver Date at all (still active, or never left) always counts.
 *   - Audited LDAs = distinct lda_id among the same filtered/scored audit
 *     rows used for the score cards (never a second, separately-derived
 *     population). LDAs With No Audits = Total LDA - Audited LDAs.
 *
 * These tests call the controller action directly rather than through the
 * HTTP route, since dashbaordCard() only needs a Request — this sidesteps
 * the page-access/auth middleware entirely while still exercising the real
 * query logic against the (in-memory sqlite) test database.
 */
class DashboardQaLdaPopulationTest extends TestCase
{
    use RefreshDatabase;

    private static int $auditSeq = 0;

    private function callCards(array $params = []): array
    {
        $response = (new DashboardControllerMain())->dashbaordCard(new Request($params));

        return $response->getData(true);
    }

    private function makeLda(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'position' => 'LDA',
            'status'   => 'active',
        ], $overrides));
    }

    private function makeAudit(string $ldaId, string $auditDate): void
    {
        self::$auditSeq++;

        DB::table('user_input_audits')->insert([
            'audit_id'         => 'TEST-AUDIT-' . self::$auditSeq,
            'lda_id'           => $ldaId,
            'audit_date_1'     => $auditDate,
            'audit_sup_name'   => 'SUP-0001',
            'auditors_name'    => 'AUD-0001',
            'invoice_id'       => 'INV-' . self::$auditSeq,
            'carrier_name'     => 'TESTCARRIER',
            'exception_status' => 'none',
            'exception_owner'  => 'none',
            'created_by'       => 'SYSTEM',
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);
    }

    public function test_active_lda_with_no_leaver_date_always_counts(): void
    {
        $this->makeLda(['employeeid' => 'EMP-0001']);

        // A range long in the past, and no filter at all — both include them.
        $this->assertSame(1, $this->callCards(['date_from' => '2020-01-01', 'date_to' => '2020-01-31'])['total_lda']);
        $this->assertSame(1, $this->callCards([])['total_lda']);
    }

    public function test_leaver_date_equal_to_start_date_is_excluded(): void
    {
        $this->makeLda([
            'employeeid' => 'EMP-0002',
            'status' => 'inactive',
            'effectivity_date_leaver' => '2026-08-31',
        ]);

        $data = $this->callCards(['date_from' => '2026-08-31', 'date_to' => '2026-09-15']);
        $this->assertSame(0, $data['total_lda']);
    }

    public function test_leaver_date_equal_to_end_date_is_included(): void
    {
        $this->makeLda([
            'employeeid' => 'EMP-0003',
            'status' => 'inactive',
            'effectivity_date_leaver' => '2026-08-31',
        ]);

        $data = $this->callCards(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);
        $this->assertSame(1, $data['total_lda']);
    }

    public function test_leaver_date_after_end_date_is_still_included(): void
    {
        // Range ends *before* the person even left, so they were clearly
        // part of the team for the whole window. This is the case a naive
        // "leaver_date <= end_date" reading of the spec would wrongly break.
        $this->makeLda([
            'employeeid' => 'EMP-0004',
            'status' => 'inactive',
            'effectivity_date_leaver' => '2026-08-31',
        ]);

        $data = $this->callCards(['date_from' => '2026-08-01', 'date_to' => '2026-08-20']);
        $this->assertSame(1, $data['total_lda']);
    }

    public function test_leaver_date_before_start_date_is_excluded(): void
    {
        $this->makeLda([
            'employeeid' => 'EMP-0005',
            'status' => 'inactive',
            'effectivity_date_leaver' => '2026-08-31',
        ]);

        $data = $this->callCards(['date_from' => '2026-09-01', 'date_to' => '2026-09-15']);
        $this->assertSame(0, $data['total_lda']);
    }

    public function test_no_date_filter_includes_every_lda_including_historical_leavers(): void
    {
        $this->makeLda(['employeeid' => 'EMP-0006']); // active
        $this->makeLda([
            'employeeid' => 'EMP-0007',
            'status' => 'inactive',
            'effectivity_date_leaver' => '2020-01-01',
        ]);

        $data = $this->callCards([]);
        $this->assertSame(2, $data['total_lda']);
    }

    public function test_the_worked_example_from_the_spec(): void
    {
        // A-D and F are active all of August; E left Aug 31 with two audits
        // logged before departing. F has zero audits this period.
        foreach (['EMP-A', 'EMP-B', 'EMP-C', 'EMP-D', 'EMP-F'] as $id) {
            $this->makeLda(['employeeid' => $id]);
        }
        $this->makeLda([
            'employeeid' => 'EMP-E',
            'status' => 'inactive',
            'effectivity_date_leaver' => '2026-08-31',
        ]);

        $this->makeAudit('EMP-A', '2026-08-05');
        $this->makeAudit('EMP-A', '2026-08-10');
        $this->makeAudit('EMP-A', '2026-08-15');
        $this->makeAudit('EMP-B', '2026-08-05');
        $this->makeAudit('EMP-B', '2026-08-10');
        $this->makeAudit('EMP-C', '2026-08-01');
        $this->makeAudit('EMP-C', '2026-08-02');
        $this->makeAudit('EMP-C', '2026-08-03');
        $this->makeAudit('EMP-C', '2026-08-04');
        $this->makeAudit('EMP-D', '2026-08-20');
        $this->makeAudit('EMP-E', '2026-08-10');
        $this->makeAudit('EMP-E', '2026-08-20');
        // EMP-F: zero audits this period, on purpose.

        $data = $this->callCards(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);

        $this->assertSame(6, $data['total_lda']);            // A, B, C, D, E, F
        $this->assertSame(5, $data['audited_ldas']);          // A, B, C, D, E
        $this->assertSame(1, $data['ldas_with_no_audits']);   // F
        $this->assertSame(12, $data['total']);                // 3+2+4+1+2 audit rows
    }

    public function test_leaver_audit_on_the_leaver_date_itself_is_not_scored(): void
    {
        $this->makeLda([
            'employeeid' => 'EMP-0008',
            'status' => 'inactive',
            'effectivity_date_leaver' => '2026-08-31',
        ]);

        $this->makeAudit('EMP-0008', '2026-08-30'); // still active — counts
        $this->makeAudit('EMP-0008', '2026-08-31'); // on the leaver date — excluded

        $data = $this->callCards(['date_from' => '2026-08-01', 'date_to' => '2026-09-15']);

        // Population still includes them (leaver_date > start_date), but
        // only the pre-leaver-date audit is scored.
        $this->assertSame(1, $data['total_lda']);
        $this->assertSame(1, $data['total']);
        $this->assertSame(1, $data['audited_ldas']);
    }

    public function test_manager_scope_restricts_population_to_their_subtree(): void
    {
        $manager = $this->makeLda([
            'employeeid' => 'EMP-MGR',
            'position' => 'Supervisor',
        ]);

        $this->makeLda(['employeeid' => 'EMP-REPORT-1', 'supervisor_id' => 'EMP-MGR']);
        $this->makeLda(['employeeid' => 'EMP-REPORT-2', 'supervisor_id' => 'EMP-MGR']);
        // An LDA outside the manager's subtree entirely.
        $this->makeLda(['employeeid' => 'EMP-OTHER']);

        $data = $this->callCards(['scope' => 'EMP-MGR']);

        $this->assertSame(2, $data['total_lda']);
    }
}
