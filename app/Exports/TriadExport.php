<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TriadExport implements FromArray, WithHeadings, ShouldAutoSize
{
    private array $criteria = [
        'body_language', 'clear_mind', 'permission_notes', 'choices_question', 'was_sme',
        'recap_summary', 'sme_adhere', 'clearly_defined', 'rca', 'line_situation',
    ];

    public function headings(): array
    {
        return ['Triad ID', 'Reference', 'Evaluator', 'Created', 'Pass', 'Fail', 'Pass Rate %'];
    }

    public function array(): array
    {
        return DB::table('triad_items as t')
            ->leftJoin('users as u', 't.created_by', '=', 'u.employeeid')
            ->select('t.reference_id', 't.reference', 't.triad', 't.created_at',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as evaluator"))
            ->orderByDesc('t.id')
            ->get()
            ->map(function ($r) {
                $triad = is_array($r->triad) ? $r->triad : (json_decode($r->triad ?? '[]', true) ?: []);
                $pass = 0; $fail = 0;
                foreach ($this->criteria as $k) {
                    $s = $triad[$k]['score'] ?? null;
                    if ($s === 'Pass') $pass++;
                    elseif ($s === 'Fail') $fail++;
                }
                $scored = $pass + $fail;
                $rate = $scored > 0 ? round($pass / $scored * 100, 1) : 0;

                return [
                    $r->reference_id,
                    $r->reference,
                    $r->evaluator,
                    $r->created_at,
                    $pass, $fail, $rate,
                ];
            })->toArray();
    }
}
