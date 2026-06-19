<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ReconExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function headings(): array
    {
        return ['Submission ID', 'Client', 'Carrier', 'Region', 'Status', 'Recon Call Date', 'LDA Email', 'Assigned To', 'Days Open'];
    }

    public function array(): array
    {
        $today = \Carbon\Carbon::today();

        return DB::table('recon_action_items')
            ->orderByDesc('id')
            ->get()
            ->map(function ($r) use ($today) {
                $isClosed = strtolower($r->status ?? '') === 'closed';
                $age = !empty($r->recon_call_date)
                    ? \Carbon\Carbon::parse($r->recon_call_date)->diffInDays($today)
                    : null;

                return [
                    $r->submission_id,
                    $r->client_code,
                    $r->carrier_code,
                    $r->region,
                    $r->status,
                    $r->recon_call_date,
                    $r->lda_email,
                    $r->assigned_to,
                    $isClosed ? '' : $age,
                ];
            })->toArray();
    }
}
