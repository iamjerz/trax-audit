<?php

namespace App\Http\Controllers;

use App\Exports\AuditTrailExport;
use App\Exports\EvaluationsExport;
use App\Exports\ReconExport;
use App\Exports\TriadExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function evaluations(Request $request)
    {
        $file = 'evaluations_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(
            new EvaluationsExport($request->input('date_from'), $request->input('date_to')),
            $file
        );
    }

    public function recon()
    {
        return Excel::download(new ReconExport(), 'action_register_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function triad()
    {
        return Excel::download(new TriadExport(), 'triad_' . now()->format('Ymd_His') . '.xlsx');
    }

    public function auditTrail(Request $request)
    {
        $filters = $request->only(['search', 'event', 'date_from', 'date_to']);
        return Excel::download(new AuditTrailExport($filters), 'audit_trail_' . now()->format('Ymd_His') . '.xlsx');
    }
}
