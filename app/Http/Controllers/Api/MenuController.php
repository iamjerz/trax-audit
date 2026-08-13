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
            ->select('employeeid', 'position')
            ->where('email', $email)
            ->first();

        if (!$user_employee) {
            return view('extension.menu', ['access' => collect(), 'pageAccess' => collect()]);
        }

        $types = \App\Support\AccessRoles::expand(
            DB::table('extension_access')
                ->where('employeeid', $user_employee->employeeid)
                ->pluck('access_type')
                ->all()
        );

        // Preserve the ->contains('access_type', X) shape the extension view uses
        $access = collect($types)->map(fn ($t) => (object) ['access_type' => $t]);

        // Which "Audit Ops Forms" buttons to show is now granted per Position
        // via page_access — same mechanism as web pages (see PageRegistry +
        // CheckPageAccess). Admins still see everything, same bypass as elsewhere.
        if (in_array('admin', $types, true)) {
            $pageAccess = collect(array_keys(\App\Support\PageRegistry::$pages));
        } elseif (! empty($user_employee->position)) {
            $pageAccess = DB::table('page_access')
                ->where('position', $user_employee->position)
                ->pluck('page_key');
        } else {
            $pageAccess = collect();
        }

        return view('extension.menu', compact('access', 'pageAccess'));
    }
}
