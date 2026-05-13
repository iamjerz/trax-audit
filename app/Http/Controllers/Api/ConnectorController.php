<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ConnectorController extends Controller
{
    //

    public function CheckConnection(){
        return response()->json([
                    'status' => 'connected'
                ], 200);
    }

    public function CheckVersion(Request $request)
    {
        $request->validate([
            'version' => 'required|string',
            'item_id' => 'required|string'
        ]);

        $data_db = DB::table('extension_details')
            ->where('item_id', $request->item_id)
            ->where('version', $request->version)
            ->where('status', 'active')
            ->first();

        if ($data_db) {
            return response()->json([
                'message' => 'Version exists',
                'status' => 'valid',
                'success' => true
            ], 200);
        }

        return response()->json([
            'message' => 'Invalid credentials',
            'status' => 'invalid',
            'success' => false
        ], 200);
    }
}
