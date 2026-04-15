<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientCodeApiController extends Controller
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

        return response()->json([
            'status' => 'success',
            'data' => array_values($data)
        ]);
    }
}
