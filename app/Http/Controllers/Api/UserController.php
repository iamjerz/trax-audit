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

        return User::select('id', 'employeeid', 'first_name', 'last_name', 'email', 'position', 'department', 'role', 'status')
            ->orderBy('id', 'desc')
            ->get();
    }
}
