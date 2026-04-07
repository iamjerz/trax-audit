<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DefaultFieldApi extends Controller
{
    public function index(Request $request)
    {


        $map = [
            'client-code' => 'client_codes',
            'carrier-code' => 'carrier_codes',
            'region' => 'region',
            'status' => 'status',
        ];

        if (!array_key_exists($request->name, $map)) {
            return response()->json(['error' => 'Invalid name'], 422);
        }

        $table = $map[$request->name];

        // Fetch data
        $data = DB::table($table)->select('name')->get(); // get all instead of first()

        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No data found',
                'data' => []
            ], 404);
        }

        return response()->json([
            'status' => 202,
            'message' => 'Success',
            'data' => $data
        ]);
    }
}