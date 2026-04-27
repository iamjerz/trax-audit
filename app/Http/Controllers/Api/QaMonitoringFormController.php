<?php

namespace App\Http\Controllers\Api;
use App\Services\DropdownService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QaMonitoringFormController extends Controller
{
    //
    public function index(DropdownService $service, Request $request)
    {

        $email = $request->email; 
        $exceptionStatus = $service->auditCondition();
        $carrierCodeND = $service->carrierCodesND();
        $Users = DB::table('users')->select('email','employeeid','first_name','last_name')->get();
        $requestor = DB::table('users')
            ->where('email', $email)
            ->select('email','employeeid','first_name','last_name')
            ->first();

        return view('extension.qa', compact(
            'Users',
            'carrierCodeND',
            'exceptionStatus',
            'requestor'
        ));
    }
}
