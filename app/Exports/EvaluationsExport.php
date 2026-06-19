<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class EvaluationsExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(private ?string $from = null, private ?string $to = null) {}

    public function headings(): array
    {
        return ['Audit ID', 'Invoice', 'LDA', 'Audit Date', 'Verification', 'Process Compliance', 'Engagement', 'Overall %', 'Created By'];
    }

    public function array(): array
    {
        $q = DB::table('user_input_audits as a')
            ->leftJoin('verifications as v', 'v.audit_id', '=', 'a.audit_id')
            ->leftJoin('process_compliances as p', 'p.audit_id', '=', 'a.audit_id')
            ->leftJoin('engagements as e', 'e.audit_id', '=', 'a.audit_id')
            ->leftJoin('users as lda', 'lda.employeeid', '=', 'a.lda_id')
            ->leftJoin('users as cb', 'cb.employeeid', '=', 'a.created_by')
            ->select(
                'a.audit_id', 'a.invoice_id', 'a.audit_date_1',
                'v.total_score as ver', 'p.total_score as proc', 'e.total_score as eng',
                DB::raw("CONCAT(lda.first_name, ' ', lda.last_name) as lda_name"),
                DB::raw("CONCAT(cb.first_name, ' ', cb.last_name) as created_by_name")
            )
            ->orderByDesc('a.id');

        if ($this->from) $q->whereDate('a.audit_date_1', '>=', $this->from);
        if ($this->to)   $q->whereDate('a.audit_date_1', '<=', $this->to);

        return $q->get()->map(function ($r) {
            $ver = (float) ($r->ver ?? 0);
            $proc = (float) ($r->proc ?? 0);
            $eng = (float) ($r->eng ?? 0);
            $overall = ($ver >= 200) ? ($proc + $eng) : 0;

            return [
                $r->audit_id,
                $r->invoice_id,
                $r->lda_name,
                $r->audit_date_1,
                $ver, $proc, $eng,
                $overall,
                $r->created_by_name,
            ];
        })->toArray();
    }
}
