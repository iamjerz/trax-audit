<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class MenuController extends Controller
{
    //

    public function index(Request $request)
    {
        $email = $request->input('email');
        
        $user_employee = DB::table('users')
            ->select('employeeid')
            ->where('email', $email)
            ->first();

        if (!$user_employee) {
            return view('extension.menu', ['access' => collect()]);
        }

        $access = DB::table('extension_access')
            ->select('access_type')
            ->where('employeeid', $user_employee->employeeid)
            ->get();

        return view('extension.menu', compact('access'));
    }
}
