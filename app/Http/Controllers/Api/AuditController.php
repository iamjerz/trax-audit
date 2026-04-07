<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\UserInputAudit;
use App\Models\Verification;
use App\Models\ProcessCompliance;
use App\Models\Engagement;
use App\Models\BusinessAnalytic;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuditController extends Controller
{

    public function store(Request $request)
    {
        $user = $request->input('userInputData');

        $raw_identifier = $user['AuditBy'];

        $validator = Validator::make(
            ['value' => $raw_identifier],
            ['value' => 'email']
        );

        if (!$validator->fails()) {

            $user_fetch = DB::table('users')
                ->select('employeeid')
                ->where('email', $raw_identifier)
                ->first(); // ✅ FIXED

            if ($user_fetch) {
                $employeeId = $user_fetch->employeeid;
            } else {
                $employeeId = null; // or fallback
            }

        } else {
            $employeeId = $raw_identifier;
        }
        


        DB::beginTransaction();

        try {
            // 🔑 Auto-generate audit_id
            $auditId = 'AUD-' . now()->format('YmdHis') . '-' . Str::random(4);

            /* =========================
            1️⃣ USER INPUT AUDIT (Parent)
            ========================== */
            

            $audit = UserInputAudit::create([
                'audit_id'         => $auditId,
                'lda_id'           => $user['ldaName'],
                'audit_date_1'     => $user['AuditDate1'],
                'audit_sup_name'   => $user['AuditSupName'],
                'auditors_name'    => $user['AuditorsName'],
                'audit_date_2'     => $user['AuditDate2'],
                'invoice_id'       => $user['InvoiceID'],
                'carrier_name'     => $user['CarrierName'],
                'exception_status' => $user['ExceptionStatus'],
                'exception_owner'  => $user['ExceptionOwner'],
                'created_by'       => $employeeId,
            ]);

            /* =========================
            2️⃣ VERIFICATION
            ========================== */
            $ver = $request->input('verificationData');

            Verification::create([
                'audit_id'        => $auditId,
                'ver_comment_1'   => $ver['VerIden1Comment'],
                'ver_outcome_1'   => (int) $ver['VerIden1Outcome'],
                'ver_comment_2'   => $ver['VerIden2Comment'],
                'ver_outcome_2'   => (int) $ver['VerIden2Outcome'],
                'total_score'     => (int) $ver['VerIden1Outcome'] + (int) $ver['VerIden2Outcome'],
                'created_by'      => $employeeId,
            ]);

            /* =========================
            3️⃣ PROCESS COMPLIANCE
            ========================== */
            $pc = $request->input('processComplianceData');

            ProcessCompliance::create([
                'audit_id'      => $auditId,
                'pc_comment_1'  => $pc['ProCom1Comment'],
                'pc_outcome_1'  => (int) $pc['ProCom1Outcome'],
                'pc_comment_2'  => $pc['ProCom2Comment'],
                'pc_outcome_2'  => (int) $pc['ProCom2Outcome'],
                'pc_comment_3'  => $pc['ProCom3Comment'],
                'pc_outcome_3'  => (int) $pc['ProCom3Outcome'],
                'pc_comment_4'  => $pc['ProCom4Comment'],
                'pc_outcome_4'  => (int) $pc['ProCom4Outcome'],
                'total_score'   =>
                    (int)$pc['ProCom1Outcome'] +
                    (int)$pc['ProCom2Outcome'] +
                    (int)$pc['ProCom3Outcome'] +
                    (int)$pc['ProCom4Outcome'],
                'created_by'    => $employeeId,
            ]);

            /* =========================
            4️⃣ ENGAGEMENT
            ========================== */
            $eng = $request->input('engagementData');

            Engagement::create([
                'audit_id'      => $auditId,
                'eng_comment_1' => $eng['engagement1Comment'],
                'eng_outcome_1' => (int) $eng['engagement1Outcome'],
                'eng_comment_2' => $eng['engagement2Comment'],
                'eng_outcome_2' => (int) $eng['engagement2Outcome'],
                'eng_comment_3' => $eng['engagement3Comment'],
                'eng_outcome_3' => (int) $eng['engagement3Outcome'],
                'eng_comment_4' => $eng['engagement4Comment'],
                'eng_outcome_4' => (int) $eng['engagement4Outcome'],
                'total_score'   =>
                    (int)$eng['engagement1Outcome'] +
                    (int)$eng['engagement2Outcome'] +
                    (int)$eng['engagement3Outcome'] +
                    (int)$eng['engagement4Outcome'],
                'created_by'    => $employeeId,
            ]);

            /* =========================
            5️⃣ BUSINESS ANALYTICS
            ========================== */
            $ba = $request->input('businessAnalyticsData');

            BusinessAnalytic::create([
                'audit_id'            => $auditId,
                'sign_carrier'        => $ba['signCarrier'],
                'follow_up'           => $ba['followUp'],
                'many_days'           => (int) $ba['manyDays'],
                'cause_issue'         => $ba['causeIssue'],
                'impact_area'         => $ba['impactArea'],
                'impact_factor'       => $ba['impactFactor'],
                'accountable_factors' => $ba['accountableFactors'],
                'root_cause'          => $ba['rootCause'],
                'created_by'          => $employeeId,
            ]);

            DB::commit();

            return response()->json([
                'message'  => 'Audit successfully created',
                'audit_id' => $auditId
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'error' => $e->getMessage(),
                'Failed' => 'Failed to Insert'
            ], 500);
        }
    }
}
