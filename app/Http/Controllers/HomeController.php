<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    /**
     * Personalized "Action Center" counts for the homepage.
     */
    public function actionCenter()
    {
        $user = auth()->user();
        $emp = $user->employeeid;

        $out = [
            'pending_ack'         => 0,
            'open_disputes'       => 0,
            'my_overdue'          => 0,
            'pending_corrections' => 0,
        ];

        // Evaluations awaiting MY acknowledgement (I'm the evaluated LDA)
        $myAuditIds = DB::table('user_input_audits')->where('lda_id', $emp)->pluck('audit_id');
        $ackedIds = (Schema::hasTable('acknowledgements'))
            ? DB::table('acknowledgements')
                ->where('reference_type', 'audit')
                ->where('employeeid', $emp)
                ->pluck('reference_id')->all()
            : [];
        $out['pending_ack'] = $myAuditIds->reject(fn ($id) => in_array($id, $ackedIds, true))->count();

        // My overdue recon action items (open 7+ days, owned by my email)
        if (! empty($user->email)) {
            $today = \Carbon\Carbon::today();
            $out['my_overdue'] = DB::table('recon_action_items')
                ->where('lda_email', $user->email)
                ->whereRaw("LOWER(COALESCE(status, '')) != 'closed'")
                ->whereNotNull('recon_call_date')
                ->pluck('recon_call_date')
                ->filter(fn ($d) => \Carbon\Carbon::parse($d)->diffInDays($today) >= 7)
                ->count();
        }

        // Open disputes awaiting review (only for reviewers)
        $access = DB::table('extension_access')->where('employeeid', $emp)->pluck('access_type')->all();
        $canReview = in_array('admin', $access, true) || in_array('web_managers', $access, true);
        if ($canReview && Schema::hasTable('disputes')) {
            $out['open_disputes'] = DB::table('disputes')->where('status', 'open')->count();
        }

        // Score corrections awaiting approval (admins or score approvers)
        if ((in_array('admin', $access, true) || in_array('web_score_approval', $access, true))
            && Schema::hasTable('score_corrections')) {
            $out['pending_corrections'] = DB::table('score_corrections')->where('status', 'pending')->count();
        }

        return response()->json($out);
    }
}
