<?php

namespace App\Http\Controllers\Api;
use App\Services\DropdownService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DropdownController extends Controller
{
    //
    public function clientCodes(DropdownService $service)
    {
        return response()->json([
            'status' => 'success',
            'data' => $service->clientCode()
        ]);
    }

    public function carrierCodes(Request $request, DropdownService $service)
    {
        return response()->json([
            'status' => 'success',
            'data' => $service->carrierCode($request->client_code)
        ]);
    }

    public function auditConditions(DropdownService $service)
    {
        return response()->json([
            'status' => 'success',
            'data' => $service->auditCondition()
        ]);
    }

    public function carrierCodesNo(DropdownService $service)
    {
        return response()->json([
            'status' => 'success',
            'data' => $service->carrierCodesND()
        ]);
    }
}
