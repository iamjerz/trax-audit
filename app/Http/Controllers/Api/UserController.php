<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Controller methods for User API can be added here
    public function usersCallApi(Request $request)
    {
        // Ensure the request expects a JSON response
        if (!$request->expectsJson()) {
            abort(403);
        }

        return User::from('users as u')
        ->leftJoin('users as s', 'u.supervisor_id', '=', 's.employeeid')
        ->select(
            'u.id',
            'u.employeeid',
            'u.first_name',
            'u.last_name',
            'u.email',
            'u.position',
            'u.department',
            'u.role',
            'u.status',
            \DB::raw("CONCAT(s.first_name, ' ', s.last_name) as supervisor_name"),
            's.employeeid as supervisor_employeeid',
            // Lets the Grid.js Actions column (user.js) hide "View as" for
            // other admin accounts — ImpersonationController enforces the
            // same rule server-side regardless, this is just so the button
            // isn't shown somewhere it's guaranteed to be rejected.
            \DB::raw("EXISTS (SELECT 1 FROM extension_access ea WHERE ea.employeeid = u.employeeid AND ea.access_type = 'admin') as is_admin")
        )
        ->orderBy('u.id', 'desc')
        ->get();
    }
}
