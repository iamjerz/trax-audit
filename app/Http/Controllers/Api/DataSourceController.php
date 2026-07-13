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
                $data = TriadItems::with([
                    'user_info:employeeid,first_name,last_name,email'
                ])->get();
                break;

            case 'coaching':
                $data = Coaching::with([
                    'user_info:employeeid,first_name,last_name,email'
                ])->get();
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
        $data = TriadItems::with([
                    'user_info:employeeid,first_name,last_name,email'
                ])->get();
        $this->logAccess('triad', $data->count());
        return response()->json($data);
    }

    public function coaching(){
        $data = Coaching::with([
                    'user_info:employeeid,first_name,last_name,email'
                ])->get();
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
