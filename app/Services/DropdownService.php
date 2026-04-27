<?php 
namespace App\Services;

class DropdownService
{
    private function getCsvData()
    {
        $path = storage_path('app/combo.csv');

        if (!file_exists($path)) {
            return [];
        }

        $rows = array_map('str_getcsv', file($path));

        $header = $rows[0];
        unset($rows[0]);

        return array_map(function ($row) use ($header) {
            return array_combine($header, $row);
        }, $rows);
    }

    public function clientCode()
    {
        $data = $this->getCsvData();

        $filtered = array_map(function ($item) {
            return [
                'client_code' => trim($item['client_code'] ?? '')
            ];
        }, $data);

        return array_values(array_filter($filtered, fn($item) => !empty($item['client_code'])));
    }

    public function carrierCode($clientCode)
    {
        $data = $this->getCsvData();

        $filtered = array_filter($data, function ($item) use ($clientCode) {
            return isset($item['combo_client_code']) &&
                trim($item['combo_client_code']) === $clientCode;
        });

        $carrierCodes = array_map(function ($item) {
            return [
                'combo_carrier_code' => trim($item['combo_carrier_code'] ?? '')
            ];
        }, $filtered);

        return array_values(array_filter($carrierCodes, fn($item) => !empty($item['combo_carrier_code'])));
    }

    // ✅ NEW FUNCTION
    public function auditCondition()
    {
        $data = $this->getCsvData();

        $filtered = array_map(function ($item) {
            return [
                'audit_condition' => trim($item['audit_condition'] ?? '')
            ];
        }, $data);

        return array_values(array_filter($filtered, fn($item) => !empty($item['audit_condition'])));
    }

    public function carrierCodesND()
    {
        $data = $this->getCsvData();

        $filtered = array_map(function ($item) {
            return [
                'carrier_code' => trim($item['carrier_code'] ?? '')
            ];
        }, $data);

        return array_values(array_filter($filtered, fn($item) => !empty($item['carrier_code'])));
    }
}




// use App\Services\DropdownService;
// use Illuminate\Http\Request;

// class DropdownController extends Controller
// {
//     public function clientCodes(DropdownService $service)
//     {
//         $data = $service->clientCode();

//         return response()->json([
//             'status' => 'success',
//             'data' => $data
//         ]);
//     }

//     public function carrierCodes(Request $request, DropdownService $service)
//     {
//         $data = $service->carrierCode($request->client_code);

//         return response()->json([
//             'status' => 'success',
//             'data' => $data
//         ]);
//     }
// }