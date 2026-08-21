<?php

namespace App\Http\Controllers\Api;
use App\Models\UserInputAudit;
use App\Models\Coaching;
use App\Models\TriadItems;
use App\Models\AuditTrail;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class DataSourceController extends Controller
{
    //

    public function index(Request $request)
    {
         $user_request = $request->input('name'); // ✅ safer

        switch ($user_request) {

            case 'qa_monitoring':
                $data = $this->resolveQaPeople(
                    UserInputAudit::with([
                        'verification',
                        'processCompliance',
                        'engagement',
                        'businessAnalytic',
                        'ldaUser:employeeid,first_name,last_name,email',
                        'auditSupervisor:employeeid,first_name,last_name,email'
                    ])->get()
                );
                break;

            case 'action_register':
                $data = DB::table('recon_action_items')->get();
                break;

            case 'triad':
                $data = $this->resolveTriadEmployees(
                    TriadItems::with([
                        'user_info:employeeid,first_name,last_name,email'
                    ])->get()
                );
                break;

            case 'coaching':
                $data = $this->resolveCoachedEmployees(
                    Coaching::with([
                        'user_info:employeeid,first_name,last_name,email'
                    ])->get()
                );
                break;

            default:
                return response()->json([
                    'message' => 'Invalid request type'
                ], 400);
        }

        $this->logAccess($user_request, $data->count());

        return response()->json($data);

    }


    public function qa_monitoring(){


        $data = $this->resolveQaPeople(
            UserInputAudit::with([
                    'verification',
                    'processCompliance',
                    'engagement',
                    'businessAnalytic',
                    'ldaUser:employeeid,first_name,last_name,email',
                    'auditSupervisor:employeeid,first_name,last_name,email'
                ])->get()
        );

        $this->logAccess('qa_monitoring', $data->count());

        return response()->json($data);

    }

    public function action_register(){
        $data = DB::table('recon_action_items')->get();
        $this->logAccess('action_register', $data->count());
        return response()->json($data);
    }

    public function triad(){
        $data = $this->resolveTriadEmployees(
            TriadItems::with([
                    'user_info:employeeid,first_name,last_name,email'
                ])->get()
        );
        $this->logAccess('triad', $data->count());
        return response()->json($data);
    }

    public function coaching(){
        $data = $this->resolveCoachedEmployees(
            Coaching::with([
                    'user_info:employeeid,first_name,last_name,email'
                ])->get()
        );
        $this->logAccess('coaching', $data->count());
        return response()->json($data);
    }

    /**
     * Replace the audit supervisor / auditor employee IDs (e.g. "EMP-202614")
     * with their full names, and add their email addresses.
     */
    private function resolveQaPeople($rows)
    {
        $ids = $rows->pluck('audit_sup_name')
            ->merge($rows->pluck('auditors_name'))
            ->filter()
            ->unique()
            ->values();

        $users = DB::table('users')
            ->whereIn('employeeid', $ids)
            ->get(['employeeid', 'first_name', 'last_name', 'email'])
            ->keyBy('employeeid');

        return $rows->map(function ($row) use ($users) {
            $sup = $users->get($row->audit_sup_name);
            $aud = $users->get($row->auditors_name);

            $data = $row->toArray();

            $data['audit_sup_name']  = $sup
                ? trim(($sup->first_name ?? '') . ' ' . ($sup->last_name ?? ''))
                : $row->audit_sup_name;
            $data['audit_sup_email'] = $sup->email ?? null;

            $data['auditors_name']   = $aud
                ? trim(($aud->first_name ?? '') . ' ' . ($aud->last_name ?? ''))
                : $row->auditors_name;
            $data['auditors_email']  = $aud->email ?? null;

            return $data;
        });
    }

    /**
     * Attach the "coached employee" (the LDA whose ticket the coaching
     * session relates to) plus a flat coach name/email to each coaching
     * row. The coachings table only stores `reference` (the underlying
     * ticket's own reference number) and `reference_type` -- since we
     * can't rely on knowing every string value `reference_type` might
     * hold, we instead try to match `reference` against each of the
     * three ticket sources directly and use whichever one hits:
     *   - QA Monitoring: user_input_audits.audit_id   -> lda_id (employeeid)
     *   - Triad:         triad_items.reference        -> created_by (employeeid)
     *   - Recon:         recon_action_items.submission_id -> lda_email (email)
     */
    private function resolveCoachedEmployees($rows)
    {
        $references = $rows->pluck('reference')->filter()->unique()->values();

        $qaMatches = DB::table('user_input_audits')
            ->whereIn('audit_id', $references)
            ->pluck('lda_id', 'audit_id');

        $triadMatches = DB::table('triad_items')
            ->whereIn('reference', $references)
            ->pluck('created_by', 'reference');

        $reconMatches = DB::table('recon_action_items')
            ->whereIn('submission_id', $references)
            ->pluck('lda_email', 'submission_id');

        $employeeIds = $qaMatches->values()
            ->merge($triadMatches->values())
            ->filter()
            ->unique()
            ->values();

        $usersByEmployeeId = DB::table('users')
            ->whereIn('employeeid', $employeeIds)
            ->get(['employeeid', 'first_name', 'last_name', 'email'])
            ->keyBy('employeeid');

        $reconEmails = $reconMatches->values()->filter()->unique()->values();

        $usersByEmail = DB::table('users')
            ->whereIn('email', $reconEmails)
            ->get(['employeeid', 'first_name', 'last_name', 'email'])
            ->keyBy('email');

        return $rows->map(function ($row) use ($qaMatches, $triadMatches, $reconMatches, $usersByEmployeeId, $usersByEmail) {
            $data = $row->toArray();

            $coachedName  = null;
            $coachedEmail = null;

            if ($row->reference && $qaMatches->has($row->reference)) {
                $u = $usersByEmployeeId->get($qaMatches->get($row->reference));
                if ($u) {
                    $coachedName  = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
                    $coachedEmail = $u->email;
                }
            } elseif ($row->reference && $triadMatches->has($row->reference)) {
                $u = $usersByEmployeeId->get($triadMatches->get($row->reference));
                if ($u) {
                    $coachedName  = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
                    $coachedEmail = $u->email;
                }
            } elseif ($row->reference && $reconMatches->has($row->reference)) {
                $email = $reconMatches->get($row->reference);
                $u = $usersByEmail->get($email);
                $coachedName  = $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : null;
                $coachedEmail = $u->email ?? $email;
            }

            $data['coached_employee_name']  = $coachedName;
            $data['coached_employee_email'] = $coachedEmail;

            $data['coach_name'] = $row->user_info
                ? trim(($row->user_info->first_name ?? '') . ' ' . ($row->user_info->last_name ?? ''))
                : null;
            $data['coach_email'] = $row->user_info->email ?? null;

            return $data;
        });
    }

    /**
     * Attach the "triad'd employee" (the LDA whose ticket the triad
     * review relates to) plus a flat reviewer name/email to each triad
     * row. Same approach as resolveCoachedEmployees(): triad_items only
     * stores `reference` (the underlying ticket's own reference number),
     * with no type column, so we match it against each candidate ticket
     * source directly and use whichever one hits:
     *   - QA Monitoring: user_input_audits.audit_id      -> lda_id (employeeid)
     *   - Recon:         recon_action_items.submission_id -> lda_email (email)
     */
    private function resolveTriadEmployees($rows)
    {
        $references = $rows->pluck('reference')->filter()->unique()->values();

        $qaMatches = DB::table('user_input_audits')
            ->whereIn('audit_id', $references)
            ->pluck('lda_id', 'audit_id');

        $reconMatches = DB::table('recon_action_items')
            ->whereIn('submission_id', $references)
            ->pluck('lda_email', 'submission_id');

        $usersByEmployeeId = DB::table('users')
            ->whereIn('employeeid', $qaMatches->values()->filter()->unique()->values())
            ->get(['employeeid', 'first_name', 'last_name', 'email'])
            ->keyBy('employeeid');

        $reconEmails = $reconMatches->values()->filter()->unique()->values();

        $usersByEmail = DB::table('users')
            ->whereIn('email', $reconEmails)
            ->get(['employeeid', 'first_name', 'last_name', 'email'])
            ->keyBy('email');

        return $rows->map(function ($row) use ($qaMatches, $reconMatches, $usersByEmployeeId, $usersByEmail) {
            $data = $row->toArray();

            $employeeName  = null;
            $employeeEmail = null;

            if ($row->reference && $qaMatches->has($row->reference)) {
                $u = $usersByEmployeeId->get($qaMatches->get($row->reference));
                if ($u) {
                    $employeeName  = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
                    $employeeEmail = $u->email;
                }
            } elseif ($row->reference && $reconMatches->has($row->reference)) {
                $email = $reconMatches->get($row->reference);
                $u = $usersByEmail->get($email);
                $employeeName  = $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : null;
                $employeeEmail = $u->email ?? $email;
            }

            $data['triad_employee_name']  = $employeeName;
            $data['triad_employee_email'] = $employeeEmail;

            $data['reviewer_name'] = $row->user_info
                ? trim(($row->user_info->first_name ?? '') . ' ' . ($row->user_info->last_name ?? ''))
                : null;
            $data['reviewer_email'] = $row->user_info->email ?? null;

            return $data;
        });
    }

    /**
     * Record a data-source pull in the audit trail.
     */
    private function logAccess(string $source, int $count): void
    {
        $labels = [
            'qa_monitoring'   => 'QA Monitoring',
            'action_register' => 'Action Register',
            'triad'           => 'Triad',
            'coaching'        => 'Coaching',
        ];
        $label = $labels[$source] ?? $source;

        AuditTrail::record([
            'event'          => 'data_accessed',
            'description'    => 'Pulled ' . $label . ' data source (' . $count . ' records)',
            'auditable_type' => 'data_source',
            'auditable_id'   => $source,
            'new_values'     => ['source' => $source, 'record_count' => $count],
        ]);
    }


}
