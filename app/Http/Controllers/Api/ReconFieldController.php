<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ReconFieldController extends Controller
{
    public function index()
    {
        $ClientCode = DB::table('client_codes')->select('name')->get();
        $CarrierCode = DB::table('carrier_codes')->select('name')->get();
        $Region = DB::table('region')->select('name')->get();
        $Status = DB::table('status')->select('name')->get();
        $Users = DB::table('users')->select('email')->get();
        return view('extension.recon', compact(
            'ClientCode',
            'CarrierCode',
            'Region',
            'Status',
            'Users'
        ));
    }
} 