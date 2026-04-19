<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ReconTiketController extends Controller
{
    //

    public function index(){

        return view("inputrecon");
    }
    public function fullDetails($id)
    {
        $data = DB::table('recon_action_items')
            ->where('submission_id', $id)
            ->first();
        $comment = DB::table('recon_item_comments as ric')
            ->leftJoin('users as u', 'ric.employeeid', '=', 'u.employeeid')
            ->where('ric.submission_id', $id)
            ->select(
                'ric.*',
                'u.first_name as employee_first_name',
                'u.last_name as employee_last_name',
            )
            ->orderBy('ric.id', 'desc') // 👈 DESC here
            ->get();
        return view("individualrecon", compact('data', 'comment'));
    }


    public function displayTicket(Request $request)
    {
        $limit = $request->input('limit', 10);
        $offset = $request->input('offset', 0);
        $search = $request->input('search');

        $query = DB::table('recon_action_items');

        // 🔍 APPLY SEARCH
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('submission_id', 'like', "%$search%")
                ->orWhere('client_code', 'like', "%$search%")
                ->orWhere('carrier_code', 'like', "%$search%")
                ->orWhere('region', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%");
            });
        }

        // ✅ IMPORTANT: count AFTER filtering
        $total = $query->count();

        // ✅ get paginated data
        $users = $query
            ->orderBy('id', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();

        return response()->json([
            'total' => $total,
            'limit' => $limit,
            'offset' => $offset,
            'data' => $users
        ]);
    }

    public function addCommentToTicket(Request $request)
    {
        $request->validate([
            'submission_id' => 'required',
            'comments' => 'required'
        ]);

        DB::table('recon_item_comments')->insert([
            'submission_id' => $request->submission_id,
            'comments'      => $request->comments,
            'employeeid'      => auth()->user()->employeeid,
            'created_at'    => now(),
            'updated_at'    => now()
        ]);
    }
}
