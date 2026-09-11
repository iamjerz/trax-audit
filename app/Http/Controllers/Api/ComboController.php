<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class ComboController extends Controller
{
    //
    public function clientCode()
    {
        $path = storage_path('app/combo.csv');

        if (!file_exists($path)) {
            return response()->json([
                'error' => 'CSV file not found'
            ], 404);
        }

        $rows = array_map('str_getcsv', file($path));

        $header = $rows[0];
        unset($rows[0]);

        $data = array_map(function ($row) use ($header) {
            return array_combine($header, $row);
        }, $rows);

        // map + trim
        $filtered = array_map(function ($item) {
            return [
                'client_code' => trim($item['client_code'] ?? '')
            ];
        }, $data);

        // remove empty
        $filtered = array_filter($filtered, function ($item) {
            return !empty($item['client_code']);
        });

        return response()->json([
            'status' => 'success',
            'data' => array_values($filtered)
        ]);
    }
    // For thi
    public function carrierCode(Request $request)
    {
        $search = $request->input('client_code'); // e.g. FRNT


        $carrierCodes = DB::table('carrier_codes')->select('name')
            ->where('client_name', $search)
            ->get();
        return response()->json([
            'status' => 'success',
            'data' => $carrierCodes
        ]);
    }
}
