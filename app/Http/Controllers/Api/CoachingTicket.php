<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;
class CoachingTicket extends Controller
{
    //
    public function getUsersData()
    {
        return [
            'logisticsUsers' => User::select('employeeid', 'first_name', 'last_name')
                ->where('position', 'Logistics Data Analyst')
                ->orderBy('first_name')
                ->get(),

            'supervisors' => User::select('employeeid', 'first_name', 'last_name')
                ->where('position', '!=', 'Logistics Data Analyst')
                ->orderBy('first_name')
                ->get(),

            'allusers' => User::select('employeeid', 'first_name', 'last_name')
                ->orderBy('first_name')
                ->get(),
        ];
    }
    public function index(){
        return view('ticketcoaching');
    }

    public function fullDetails($id)
    {

        $usersData = $this->getUsersData();


        $data = DB::table('coachings')
            ->where('reference_id', $id)
            ->first();

        $created_by = DB::table('users as u')
        ->join('coachings as r', 'r.created_by', '=', 'u.employeeid')
        ->select('u.first_name as FirstName', 'u.last_name as LastName')
        ->where('r.reference_id', $id)
        ->first(); 
        return view("individualcoaching", compact('data', 'usersData', 'created_by'));
    }

    public function displayTicket(Request $request)
    {
        try {
            $user_email = auth()->user()->email;
            $user_position = auth()->user()->position;
            $user_employeeid = auth()->user()->employeeid;

            $limit = $request->input('limit', 10);
            $offset = $request->input('offset', 0);
            $search = $request->input('search');

            $query = DB::table('coachings')
                ->leftJoin('users', 'coachings.created_by', '=', 'users.employeeid')
                ->select(
                    'coachings.*',
                    DB::raw("users.first_name || ' ' || users.last_name as full_name")
                );

            // 🔍 SEARCH (PostgreSQL-safe with ILIKE)
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('coachings.reference', 'ilike', "%{$search}%")
                    ->orWhere('coachings.reference_id', 'ilike', "%{$search}%")

                    // 🔥 NAME SEARCH
                    ->orWhere('users.first_name', 'ilike', "%{$search}%")
                    ->orWhere('users.last_name', 'ilike', "%{$search}%")
                    ->orWhere(DB::raw("users.first_name || ' ' || users.last_name"), 'ilike', "%{$search}%");
                });
            }

            // 👤 ROLE FILTER
            if ($user_position == "Logistics Data Analyst") {
                $query->where(function ($q) use ($user_email, $user_employeeid) {
                    $q->where('coachings.created_by', $user_email)
                    ->orWhere('coachings.created_by', $user_employeeid);
                });
            }

            // ✅ COUNT
            $total = (clone $query)->count();

            // ✅ DATA
            $data = $query
                ->orderBy('coachings.id', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get();

            return response()->json([
                'data' => $data,
                'total' => $total
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
