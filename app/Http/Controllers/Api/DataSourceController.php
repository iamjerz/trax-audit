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
                $data = UserInputAudit::with([
                    'verification',
                    'processCompliance',
                    'engagement',
                    'businessAnalytic',
                    'ldaUser:employeeid,first_name,last_name,email',
                    'auditSupervisor:employeeid,first_name,last_name,email'
                ])->get();
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


        $data = UserInputAudit::with([
                    'verification',
                    'processCompliance',
                    'engagement',
                    'businessAnalytic',
                    'ldaUser:employeeid,first_name,last_name,email',
                    'auditSupervisor:employeeid,first_name,last_name,email'
                ])->get();

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
