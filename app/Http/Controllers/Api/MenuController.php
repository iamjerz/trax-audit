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

        $types = \App\Support\AccessRoles::expand(
            DB::table('extension_access')
                ->where('employeeid', $user_employee->employeeid)
                ->pluck('access_type')
                ->all()
        );

        // Preserve the ->contains('access_type', X) shape the extension view uses
        $access = collect($types)->map(fn ($t) => (object) ['access_type' => $t]);

        return view('extension.menu', compact('access'));
    }
}
