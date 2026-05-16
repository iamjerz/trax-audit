<?php

namespace App\Http\Controllers\Api;
use App\Models\UserInputAudit;
use App\Models\Coaching;
use App\Models\TriadItems;
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

        return response()->json($data);
        
    }
}
