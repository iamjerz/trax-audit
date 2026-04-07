<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QaMonitoringFormController extends Controller
{
    //
    public function index()
    {
        $ClientCode = DB::table('client_codes')->select('name')->get();
        $CarrierCode = DB::table('carrier_codes')->select('name')->get();
        $Region = DB::table('region')->select('name')->get();
        $Status = DB::table('status')->select('name')->get();
        $Users = DB::table('users')->select('email','employeeid','first_name','last_name')->get();
        return view('extension.qa', compact(
            'ClientCode',
            'CarrierCode',
            'Region',
            'Status',
            'Users'
        ));
    }
}
