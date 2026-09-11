<?php

namespace App\Exports;

use App\Support\PositionScope;
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
        $query = DB::table('triad_items as t')
            ->leftJoin('users as u', 't.created_by', '=', 'u.employeeid')
            ->select('t.reference_id', 't.reference', 't.triad', 't.created_at',
                DB::raw("CONCAT(u.first_name, ' ', u.last_name) as evaluator"));

        // 👤 LEVEL FILTER — scope comes from the positions table (see
        // App\Support\PositionScope), not a hardcoded string match. Mirrors
        // TriadTicket::displayTicket() exactly — this export previously had
        // no user-scoping at all, so it dumped every triad_items row.
        $user = auth()->user();
        if ($user) {
            $scope = PositionScope::forPosition($user->position);

            if ($scope === 'own') {
                $query->where(function ($q) use ($user) {
                    $q->where('t.created_by', $user->email)
                      ->orWhere('t.created_by', $user->employeeid);
                });
            } elseif ($scope === 'team') {
                $query->where('u.supervisor_id', $user->employeeid);
            }
        }

        return $query
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
