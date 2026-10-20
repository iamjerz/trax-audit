<?php

namespace App\Http\Controllers\Api;

use App\Exports\UsersBulkEditExport;
use App\Http\Controllers\Controller;
use App\Imports\UsersBulkEditImport;
use App\Models\AuditTrail;
use App\Services\UserBulkEditService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Backs the /users "Bulk Edit (Excel)" modal — see UserBulkEditService for
 * the actual validation/commit rules. Three steps, three endpoints (all
 * inside the same 'access:admin' route group as the rest of /users):
 *   1. template() — download a fresh export shaped for round-tripping.
 *   2. preview()  — upload the edited file; validates every row and stores
 *      the resulting plan under a short-lived token. No database writes yet.
 *   3. commit()   — given that token, re-loads the exact validated plan and
 *      applies it. Nothing is re-parsed from the spreadsheet at this point,
 *      so preview and commit can never disagree with each other.
 */
class UserBulkImportController extends Controller
{
    public function template()
    {
        return Excel::download(
            new UsersBulkEditExport(),
            'users_bulk_edit_template_' . now()->format('Ymd_His') . '.xlsx'
        );
    }

    public function preview(Request $request, UserBulkEditService $service)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        $this->sweepOldTempFiles();

        $sheets = Excel::toCollection(new UsersBulkEditImport(), $request->file('file'));
        $rows = $sheets->first() ?? collect();

        $plan = $service->validateRows($rows);

        $token = (string) Str::uuid();
        file_put_contents($this->tempPath($token), json_encode($plan));

        return response()->json([
            'token' => $token,
            'summary' => $plan['summary'],
            'rows' => $plan['results'],
        ]);
    }

    public function commit(Request $request, UserBulkEditService $service)
    {
        $request->validate(['token' => 'required|string']);

        $path = $this->tempPath(basename($request->input('token')));
        if (! file_exists($path)) {
            return response()->json([
                'success' => false,
                'message' => 'This preview has expired. Please upload the file again.',
            ], 410);
        }

        $plan = json_decode(file_get_contents($path), true);
        @unlink($path);

        $result = $service->commit($plan);

        AuditTrail::record([
            'event' => 'bulk_user_import',
            'description' => "Bulk user import via Excel: {$result['created']} created, {$result['updated']} updated"
                . ($result['failed'] ? ", {$result['failed']} failed" : ''),
            'auditable_type' => 'user',
            'new_values' => [
                'created' => $result['created'],
                'updated' => $result['updated'],
                'failed' => $result['failed'],
                'created_ids' => $result['createdIds'],
                'updated_ids' => $result['updatedIds'],
            ],
        ]);

        return response()->json([
            'success' => true,
            'created' => $result['created'],
            'updated' => $result['updated'],
            'failed' => $result['failed'],
            'errors' => $result['errors'],
        ]);
    }

    private function tempDir(): string
    {
        $dir = storage_path('app/temp/bulk-user-import');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private function tempPath(string $token): string
    {
        return $this->tempDir() . "/{$token}.json";
    }

    // Abandoned previews (uploaded, never confirmed) are harmless but no
    // reason to let them pile up forever — swept on the next preview call
    // rather than needing a separate scheduled job for one small temp dir.
    private function sweepOldTempFiles(): void
    {
        foreach (glob($this->tempDir() . '/*.json') ?: [] as $file) {
            if (filemtime($file) < now()->subDay()->timestamp) {
                @unlink($file);
            }
        }
    }
}
