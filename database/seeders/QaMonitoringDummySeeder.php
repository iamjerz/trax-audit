<?php

namespace Database\Seeders;

use App\Models\BusinessAnalytic;
use App\Models\Engagement;
use App\Models\ProcessCompliance;
use App\Models\User;
use App\Models\UserInputAudit;
use App\Models\Verification;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QaMonitoringDummySeeder extends Seeder
{
    /**
     * Number of dummy audit tickets to generate.
     */
    private const COUNT = 50;

    /**
     * Run the database seeds.
     *
     * Generates full, internally-consistent QA Monitoring audits across all
     * 5 related tables (user_input_audits + verifications +
     * process_compliances + engagements + business_analytics), reusing the
     * exact option sets the real QA form uses so the data looks and scores
     * the same way a real submission would.
     */
    public function run(): void
    {
        // The person being audited must actually be an LDA.
        $ldaIds = User::where('position', 'LDA')->pluck('employeeid')->all();

        // Supervisor / auditor / creator can be anyone else (sup, manager, etc.).
        $staffIds = User::where('position', '!=', 'LDA')->pluck('employeeid')->all();

        if (empty($ldaIds)) {
            $this->command?->warn('No users with position = LDA found — skipping QA monitoring dummy data.');
            return;
        }

        if (empty($staffIds)) {
            // Fall back so the seeder can still run on a near-empty users table.
            $staffIds = $ldaIds;
        }

        $carriers = ['FEDEX', 'UPS', 'USPS', 'DHL', 'ONTRAC', 'OLD DOMINION', 'XPO', 'ESTES', 'SAIA', 'R+L CARRIERS'];
        $clientCodes = ['CLT-1001', 'CLT-1002', 'CLT-1003', 'CLT-1004', 'CLT-1005', 'CLT-1006', 'CLT-1007', 'CLT-1008'];

        // exception_status has no fixed option list in the app (it's CSV-driven
        // via DropdownService::auditCondition()) — using plausible values.
        $exceptionStatuses = ['Compliant', 'Non-Compliant', 'Pending Review', 'Escalated', 'Resolved'];

        // These 6 lists match the real <select> options in extension/qa.blade.php exactly.
        $exceptionOwners = ['Carrier Review', 'Client Review', 'Trax Review'];
        $causeIssues = ['Carrier Data Issue', 'Rating Issue', 'Delayed in Response', 'Incorrect Action Taken'];
        $impactAreas = ['System', 'Tools/Technology', 'People', 'Processes'];
        $impactFactors = [
            'People - Employee actions', 'People - Training Gap', 'People - Behaviour',
            'People - Decision- Making', 'Processes - Workflow', 'Processes - Procedures',
            'Processes - Protocols or Operational guidelines', 'Tools/Technology - Software',
            'Tools/Technology - Systems', 'Systems - Internal systems or processes',
        ];
        $accountableFactors = ['Carrier', 'Client - Procurement', 'Client', 'Trax LDA', 'Trax Internal Team'];
        $rootCauses = ['Controllable', 'Uncontrollable'];

        // Per-field rating scales, matching each outcome <select>'s real option values.
        $verificationScale = [100, 0];              // Pass / Fail
        $pc1Scale = [10, 5, 0];                     // Met / Coached / Not Met
        $pc2Scale = [15, 8, 0];
        $pc3Scale = [15, 8, 0];
        $pc4Scale = [10, 5, 0];
        $eng1Scale = [10, 5, 0];
        $eng2Scale = [10, 5, 0];
        $eng3Scale = [15, 8, 0];
        $eng4Scale = [15, 8, 0];

        $created = 0;
        $attempts = 0;

        while ($created < self::COUNT && $attempts < self::COUNT * 4) {
            $attempts++;

            $auditId = 'AUD-' . now()->format('YmdHis') . '-' . Str::random(4);
            $auditDate = now()->subDays(random_int(0, 180))->format('Y-m-d');

            $ldaId = $ldaIds[array_rand($ldaIds)];
            $auditorId = $staffIds[array_rand($staffIds)];
            $supId = $staffIds[array_rand($staffIds)];

            try {
                DB::transaction(function () use (
                    $auditId, $auditDate, $ldaId, $auditorId, $supId,
                    $carriers, $clientCodes, $exceptionStatuses, $exceptionOwners,
                    $causeIssues, $impactAreas, $impactFactors, $accountableFactors, $rootCauses,
                    $verificationScale, $pc1Scale, $pc2Scale, $pc3Scale, $pc4Scale,
                    $eng1Scale, $eng2Scale, $eng3Scale, $eng4Scale
                ) {
                    UserInputAudit::create([
                        'audit_id'         => $auditId,
                        'lda_id'           => $ldaId,
                        'audit_date_1'     => $auditDate,
                        'audit_sup_name'   => $supId,
                        'auditors_name'    => $auditorId,
                        'audit_date_2'     => $auditDate,
                        'invoice_id'       => 'INV-' . random_int(100000, 999999),
                        'carrier_name'     => $carriers[array_rand($carriers)],
                        'client_code'      => $clientCodes[array_rand($clientCodes)],
                        'exception_status' => $exceptionStatuses[array_rand($exceptionStatuses)],
                        'exception_owner'  => $exceptionOwners[array_rand($exceptionOwners)],
                        'is_calibration'   => random_int(1, 100) <= 25,
                        'created_by'       => $auditorId,
                    ]);

                    $ver1 = $verificationScale[array_rand($verificationScale)];
                    $ver2 = $verificationScale[array_rand($verificationScale)];

                    Verification::create([
                        'audit_id'      => $auditId,
                        'ver_comment_1' => fake()->realText(random_int(60, 300)),
                        'ver_outcome_1' => $ver1,
                        'ver_comment_2' => fake()->realText(random_int(60, 300)),
                        'ver_outcome_2' => $ver2,
                        'total_score'   => $ver1 + $ver2,
                        'created_by'    => $auditorId,
                    ]);

                    $pc1 = $pc1Scale[array_rand($pc1Scale)];
                    $pc2 = $pc2Scale[array_rand($pc2Scale)];
                    $pc3 = $pc3Scale[array_rand($pc3Scale)];
                    $pc4 = $pc4Scale[array_rand($pc4Scale)];

                    ProcessCompliance::create([
                        'audit_id'     => $auditId,
                        'pc_comment_1' => fake()->realText(random_int(60, 300)),
                        'pc_outcome_1' => $pc1,
                        'pc_comment_2' => fake()->realText(random_int(60, 300)),
                        'pc_outcome_2' => $pc2,
                        'pc_comment_3' => fake()->realText(random_int(60, 300)),
                        'pc_outcome_3' => $pc3,
                        'pc_comment_4' => fake()->realText(random_int(60, 300)),
                        'pc_outcome_4' => $pc4,
                        'total_score'  => $pc1 + $pc2 + $pc3 + $pc4,
                        'created_by'   => $auditorId,
                    ]);

                    $eng1 = $eng1Scale[array_rand($eng1Scale)];
                    $eng2 = $eng2Scale[array_rand($eng2Scale)];
                    $eng3 = $eng3Scale[array_rand($eng3Scale)];
                    $eng4 = $eng4Scale[array_rand($eng4Scale)];

                    Engagement::create([
                        'audit_id'      => $auditId,
                        'eng_comment_1' => fake()->realText(random_int(60, 300)),
                        'eng_outcome_1' => $eng1,
                        'eng_comment_2' => fake()->realText(random_int(60, 300)),
                        'eng_outcome_2' => $eng2,
                        'eng_comment_3' => fake()->realText(random_int(60, 300)),
                        'eng_outcome_3' => $eng3,
                        'eng_comment_4' => fake()->realText(random_int(60, 300)),
                        'eng_outcome_4' => $eng4,
                        'total_score'   => $eng1 + $eng2 + $eng3 + $eng4,
                        'created_by'    => $auditorId,
                    ]);

                    BusinessAnalytic::create([
                        'audit_id'            => $auditId,
                        'sign_carrier'        => random_int(0, 1) ? 'Yes' : 'No',
                        'follow_up'           => random_int(0, 1) ? 'Yes' : 'No',
                        'many_days'           => random_int(1, 30),
                        'cause_issue'         => $causeIssues[array_rand($causeIssues)],
                        'impact_area'         => $impactAreas[array_rand($impactAreas)],
                        'impact_factor'       => $impactFactors[array_rand($impactFactors)],
                        'accountable_factors' => $accountableFactors[array_rand($accountableFactors)],
                        'root_cause'          => $rootCauses[array_rand($rootCauses)],
                        'created_by'          => $auditorId,
                    ]);
                });

                $created++;
            } catch (\Throwable $e) {
                // Extremely unlikely audit_id collision — just retry with a fresh id.
                continue;
            }
        }

        $this->command?->info("Seeded {$created} QA Monitoring dummy audits.");
    }
}
