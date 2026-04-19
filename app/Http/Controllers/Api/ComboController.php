<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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

        // 👉 filter by request (FRNT)
        $filtered = array_filter($data, function ($item) use ($search) {
            return isset($item['combo_client_code']) &&
                trim($item['combo_client_code']) === $search;
        });

        // 👉 extract only carrier code
        $carrierCodes = array_map(function ($item) {
            return [
                'combo_carrier_code' => trim($item['combo_carrier_code'] ?? '')
            ];
        }, $filtered);

        // 👉 remove empty (optional)
        $carrierCodes = array_filter($carrierCodes, function ($item) {
            return !empty($item['combo_carrier_code']);
        });

        return response()->json([
            'status' => 'success',
            'data' => array_values($carrierCodes)
        ]);
    }
}
