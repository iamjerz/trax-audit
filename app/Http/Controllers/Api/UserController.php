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
            's.employeeid as supervisor_employeeid'
        )
        ->orderBy('u.id', 'desc')
        ->get();
    }
}
