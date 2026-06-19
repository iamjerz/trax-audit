<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LdaScorecardController extends Controller
{
    private array $triadCriteria = [
        'body_language', 'clear_mind', 'permission_notes', 'choices_question', 'was_sme',
        'recap_summary', 'sme_adhere', 'clearly_defined', 'rca', 'line_situation',
    ];

    public function index(Request $request)
    {
        $employeeid = $request->input('employeeid');

        $ldas = User::whereIn('position', ['LDA', 'Logistics Data Analyst'])
            ->orderBy('first_name')
            ->get(['employeeid', 'first_name', 'last_name', 'email']);

        $scorecard = null;

        if ($employeeid) {
            $lda = User::where('employeeid', $employeeid)->first();

            if ($lda) {
                // QA evaluations for this LDA
                $audits = DB::table('user_input_audits as a')
                    ->leftJoin('verifications as v', 'v.audit_id', '=', 'a.audit_id')
                    ->leftJoin('process_compliances as p', 'p.audit_id', '=', 'a.audit_id')
                    ->leftJoin('engagements as e', 'e.audit_id', '=', 'a.audit_id')
                    ->where('a.lda_id', $employeeid)
                    ->select('a.audit_id', 'a.invoice_id', 'a.audit_date_1',
                        'v.total_score as ver', 'p.total_score as proc', 'e.total_score as eng')
                    ->orderByDesc('a.id')
                    ->get();

                $count = $audits->count();
                $above = 0;
                $sum = 0;

                foreach ($audits as $a) {
                    $ver = (float) ($a->ver ?? 0);
                    $proc = (float) ($a->proc ?? 0);
                    $eng = (float) ($a->eng ?? 0);
                    $a->overall = ($ver >= 200) ? ($proc + $eng) : 0;
                    $sum += $a->overall;
                    if ($a->overall >= 75) $above++;
                }

                // Triads about this LDA's audits
                $triads = DB::table('triad_items as t')
                    ->join('user_input_audits as a', 'a.audit_id', '=', 't.reference')
                    ->where('a.lda_id', $employeeid)
                    ->pluck('t.triad');

                $tPass = 0; $tFail = 0;
                foreach ($triads as $tj) {
                    $arr = is_array($tj) ? $tj : (json_decode($tj ?? '[]', true) ?: []);
                    foreach ($this->triadCriteria as $k) {
                        $s = $arr[$k]['score'] ?? null;
                        if ($s === 'Pass') $tPass++;
                        elseif ($s === 'Fail') $tFail++;
                    }
                }
                $triadScored = $tPass + $tFail;

                // Coaching sessions about this LDA's audits
                $coachingCount = DB::table('coachings as c')
                    ->join('user_input_audits as a', 'a.audit_id', '=', 'c.reference')
                    ->where('a.lda_id', $employeeid)
                    ->count();

                // Open recon items assigned to / owned by this LDA
                $openRecon = DB::table('recon_action_items')
                    ->where('lda_email', $lda->email)
                    ->whereRaw("LOWER(COALESCE(status, '')) != 'closed'")
                    ->count();

                $scorecard = [
                    'lda'             => $lda,
                    'eval_count'      => $count,
                    'avg_score'       => $count ? round($sum / $count, 1) : 0,
                    'pass_rate'       => $count ? round($above / $count * 100, 1) : 0,
                    'above'           => $above,
                    'below'           => $count - $above,
                    'triad_count'     => $triads->count(),
                    'triad_pass_rate' => $triadScored ? round($tPass / $triadScored * 100, 1) : 0,
                    'coaching_count'  => $coachingCount,
                    'open_recon'      => $openRecon,
                    'recent'          => $audits->take(10),
                ];
            }
        }

        return view('ldascorecard', compact('ldas', 'scorecard', 'employeeid'));
    }
}
