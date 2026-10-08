<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        // Validate the whole payload shape up front. Previously every field
        // was pulled straight out of raw arrays with no check that the
        // top-level keys (userInputData, verificationData, etc.) — or their
        // nested fields — even existed. A missing userInputData used to
        // throw a fatal, uncaught TypeError (array access on null) before
        // the try/catch below even started; a missing/malformed outcome
        // score used to silently become 0 via the (int) casts further down
        // instead of failing. Both now return a clean 422 instead.
        $validator = Validator::make($request->all(), [
            'userInputData' => 'required|array',
            'userInputData.AuditBy' => 'required|string',
            'userInputData.ldaName' => 'nullable|string',
            'userInputData.AuditDate1' => 'required|date',
            'userInputData.AuditSupName' => 'nullable|string',
            'userInputData.AuditorsName' => 'nullable|string',
            'userInputData.AuditDate2' => 'nullable|date',
            'userInputData.InvoiceID' => 'nullable|string',
            'userInputData.CarrierName' => 'nullable|string',
            'userInputData.ClientCode' => 'nullable|string',
            'userInputData.ExceptionStatus' => 'nullable|string',
            'userInputData.ExceptionOwner' => 'nullable|string',
            'userInputData.IsCalibration' => 'nullable|boolean',

            'verificationData' => 'required|array',
            'verificationData.VerIden1Comment' => 'nullable|string',
            'verificationData.VerIden1Outcome' => 'required|numeric',
            'verificationData.VerIden2Comment' => 'nullable|string',
            'verificationData.VerIden2Outcome' => 'required|numeric',

            'processComplianceData' => 'required|array',
            'processComplianceData.ProCom1Comment' => 'nullable|string',
            'processComplianceData.ProCom1Outcome' => 'required|numeric',
            'processComplianceData.ProCom2Comment' => 'nullable|string',
            'processComplianceData.ProCom2Outcome' => 'required|numeric',
            'processComplianceData.ProCom3Comment' => 'nullable|string',
            'processComplianceData.ProCom3Outcome' => 'required|numeric',
            'processComplianceData.ProCom4Comment' => 'nullable|string',
            'processComplianceData.ProCom4Outcome' => 'required|numeric',

            'engagementData' => 'required|array',
            'engagementData.engagement1Comment' => 'nullable|string',
            'engagementData.engagement1Outcome' => 'required|numeric',
            'engagementData.engagement2Comment' => 'nullable|string',
            'engagementData.engagement2Outcome' => 'required|numeric',
            'engagementData.engagement3Comment' => 'nullable|string',
            'engagementData.engagement3Outcome' => 'required|numeric',
            'engagementData.engagement4Comment' => 'nullable|string',
            'engagementData.engagement4Outcome' => 'required|numeric',

            'businessAnalyticsData' => 'required|array',
            'businessAnalyticsData.signCarrier' => 'nullable|string',
            'businessAnalyticsData.followUp' => 'nullable|string',
            'businessAnalyticsData.manyDays' => 'nullable|numeric',
            'businessAnalyticsData.causeIssue' => 'nullable|string',
            'businessAnalyticsData.impactArea' => 'nullable|string',
            'businessAnalyticsData.impactFactor' => 'nullable|string',
            'businessAnalyticsData.accountableFactors' => 'nullable|string',
            'businessAnalyticsData.rootCause' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->input('userInputData');

        // Resolve AuditBy -> a real employeeid, whether it was sent as an
        // email or as an employeeid directly. Previously: an email that
        // didn't match any user silently became created_by = null, and
        // anything that *didn't* look like an email was trusted as-is with
        // no existence check at all. Both now reject the request instead —
        // ownership (My Evaluations, acknowledgements, scorecards) depends
        // on created_by always being a real employeeid.
        $raw_identifier = trim((string) $user['AuditBy']);

        $looksLikeEmail = !Validator::make(
            ['value' => $raw_identifier],
            ['value' => 'email']
        )->fails();

        if ($looksLikeEmail) {
            $employeeId = DB::table('users')
                ->where('email', strtolower($raw_identifier))
                ->value('employeeid');
        } else {
            $employeeId = DB::table('users')
                ->where('employeeid', $raw_identifier)
                ->value('employeeid');
        }

        if (!$employeeId) {
            return response()->json([
                'success' => false,
                'errors' => ['userInputData.AuditBy' => ["Could not match \"{$raw_identifier}\" to an existing user."]],
            ], 422);
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
                'client_code'      => $user['ClientCode'] ?? null,
                'exception_status' => $user['ExceptionStatus'],
                'exception_owner'  => $user['ExceptionOwner'],
                'is_calibration'   => $user['IsCalibration'] ?? false,
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

            // Previously nothing was ever logged here — a real production
            // failure would only ever be visible in whatever the extension
            // did with the raw JSON error response, with zero server-side
            // trace of it happening at all.
            Log::error('QA form submission failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'audit_id' => $auditId ?? null,
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'Failed' => 'Failed to Insert'
            ], 500);
        }
    }
}
