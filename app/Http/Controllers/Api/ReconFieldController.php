<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ReconFieldController extends Controller
{
    public function index()
    {
        $clientCodes = DB::table('client_codes')->select('name')->get();
        $carrierCodes = DB::table('carrier_codes')->select('name')->get();
        $regions = DB::table('region')->select('name')->get();
        $statuses = DB::table('status')->select('name')->get();
        $users = DB::table('users')->select('email')->get();

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

        // map + trim (⚠️ use correct column name)
        $filtered = array_map(function ($item) {
            return [
                'client_code' => trim($item['client_code'] ?? '')
            ];
        }, $data);

        // remove empty
        $filtered = array_filter($filtered, function ($item) {
            return !empty($item['client_code']);
        });

        // remove duplicates
        $unique = [];
        $filtered = array_filter($filtered, function ($item) use (&$unique) {
            if (in_array($item['client_code'], $unique)) {
                return false;
            }
            $unique[] = $item['client_code'];
            return true;
        });

        $carrier_code_dr = array_values($filtered);

        return view('extension.recon', compact(
            'clientCodes',
            'carrierCodes',
            'regions',
            'statuses',
            'users',
            'carrier_code_dr'
        ));
    }
} 