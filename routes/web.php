<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\AuditTrailController;
use App\Http\Controllers\ExtensionDetailController;
use App\Http\Controllers\AcknowledgementController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\LdaScorecardController;
use App\Http\Controllers\MyEvaluationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DisputeController;
use App\Http\Controllers\ScoreCorrectionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\CorrectionApprovalController;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DisplayFormListController;
use App\Http\Controllers\FormInsertController;
use App\Http\Controllers\FormBuilderController;
use App\Http\Controllers\UserImportController;
use App\Http\Controllers\UserListMonitoringPage;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\DashboardControllerMain;
use App\Http\Controllers\EvalIndividual;
use App\Http\Controllers\ViewTicket;
use App\Http\Controllers\Api\CoachingTriadController;
use App\Http\Controllers\Api\DefaultFieldApi;
use App\Http\Controllers\Api\ReconFieldController;
use App\Http\Controllers\Api\LoginVerifyController;
use App\Http\Controllers\Api\ReconActionItemController;
use App\Http\Controllers\Api\QaMonitoringFormController;
use App\Http\Controllers\Api\CoachingFormController;
use App\Http\Controllers\Api\TriadItemController;
use App\Http\Controllers\Api\CoachingController;
use App\Http\Controllers\Api\ReconTiketController;
use App\Http\Controllers\Api\DashboardReconController;
use App\Http\Controllers\Api\DashboardTriadController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\TriadTicket;
use App\Http\Controllers\Api\UserPageController;
use App\Http\Controllers\Api\CoachingTicket;
/*
|--------------------------------------------------------------------------
| 
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('homepage')
        : redirect()->route('login');
});

Route::get('/import-users', [UserImportController::class, 'import']);

/*
|--------------------------------------------------------------------------
| Guest Routes (NOT logged in)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {

    Route::get('/login', function () {
        return Auth::check()
            ? redirect()->route('homepage')
            : view('login');
    })->name('login');

    Route::post('/login', [LoginController::class, 'authenticate'])
        ->name('login.attempt');
});

/*
|--------------------------------------------------------------------------
| Authenticated Routes (logged in)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    /* -----------------------------------------------------------------
     | Always available to any authenticated user
     | ----------------------------------------------------------------*/
    Route::get('/homepage', function () {
        return view('homepage');
    })->name('homepage');

    // Force password change (default-password users are redirected here by middleware)
    Route::get('/password/change', [ChangePasswordController::class, 'show'])
        ->name('password.change');
    Route::post('/password/change', [ChangePasswordController::class, 'update'])
        ->name('password.update');

    // My Evaluations — every user can view and acknowledge their own evaluations
    Route::get('/home/action-center', [HomeController::class, 'actionCenter'])->name('home.action-center');

    Route::get('/my-evaluations', [MyEvaluationController::class, 'index'])->name('my-evaluations');
    Route::get('/my-evaluations/{auditId}', [MyEvaluationController::class, 'show'])->name('my-evaluations.show');
    Route::post('/my-evaluations/{auditId}/acknowledge', [MyEvaluationController::class, 'acknowledge'])
        ->name('my-evaluations.acknowledge');
    Route::post('/my-evaluations/{auditId}/dispute', [MyEvaluationController::class, 'dispute'])
        ->name('my-evaluations.dispute');

    // Logout
    Route::post('/logout', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    /* -----------------------------------------------------------------
     | Administrator only
     | ----------------------------------------------------------------*/
    Route::middleware('access:admin')->group(function () {
        Route::get('/audit-trail', [AuditTrailController::class, 'index'])->name('audit-trail');
        Route::get('/export/audit-trail', [ExportController::class, 'auditTrail'])->name('export.audit-trail');

        // Extension Details management
        Route::get('/extension-details', [ExtensionDetailController::class, 'index'])->name('extension-details');
        Route::post('/extension-details', [ExtensionDetailController::class, 'store']);
        Route::put('/extension-details/{id}', [ExtensionDetailController::class, 'update']);
        Route::get('/extension-details/{id}/history', [ExtensionDetailController::class, 'history']);

        Route::get('/users', [UserListMonitoringPage::class, 'UserPageList']);
        Route::get('/users/data', [UserController::class, 'usersCallApi'])->name('users.data');
        Route::get('/check-email', [UserPageController::class, 'check']);
        Route::post('/insert-user', [UserPageController::class, 'store']);
        Route::get('/edit-user/{employeeid}', [UserPageController::class, 'index']);
        Route::put('/users/edit/{employeeid}', [UserPageController::class, 'updateUser'])->name('users.update');
        Route::put('/users/{employeeid}/access', [UserPageController::class, 'updateAccessOnly']);
        Route::put('/users/{employeeid}/reset-password', [UserPageController::class, 'resetPassword']);
    });

    /* -----------------------------------------------------------------
     | Manager Tools — Score correction approvals (web_score_approval)
     | ----------------------------------------------------------------*/
    Route::middleware('access:web_score_approval')->group(function () {
        Route::get('/reports/corrections', [CorrectionApprovalController::class, 'index'])->name('reports.corrections');
        Route::post('/reports/corrections/{id}/approve', [CorrectionApprovalController::class, 'approve'])->name('reports.corrections.approve');
        Route::post('/reports/corrections/{id}/reject', [CorrectionApprovalController::class, 'reject'])->name('reports.corrections.reject');
    });

    /* -----------------------------------------------------------------
     | Dashboards (web_dashboard)
     | ----------------------------------------------------------------*/
    Route::middleware('access:web_dashboard,web_managers')->group(function () {
        Route::get('/dashboard-qa', function () {
            return view('dashboard');
        });
        Route::get('/dashboard/recent-ticket', [DashboardControllerMain::class, 'dashboardRecentTableTicket']);
        Route::get('/dashboard/accountable-factor', [DashboardControllerMain::class, 'impact_factor_count']);
        Route::get('/dashboard/cause-issue', [DashboardControllerMain::class, 'cause_issue_count']);
        Route::get('/dashboard/root-cause', [DashboardControllerMain::class, 'root_cause_count']);
        Route::get('/dashboard/trend', [DashboardControllerMain::class, 'trend']);

        Route::get('/dashboard-recon', [DashboardReconController::class, 'index']);
        Route::get('/dashboard-recon-aging', [DashboardReconController::class, 'Aging']);
        Route::get('/dashboard-recon-table-top10', [DashboardReconController::class, 'Top10Breakdown']);
        Route::get('/dashboard-recon-chart-clientcode', [DashboardReconController::class, 'TopClientsChart']);
        Route::get('/dashboard-recon-chart-carriercode', [DashboardReconController::class, 'TopCarriers']);

        Route::get('/dashboard-triad', [DashboardTriadController::class, 'index']);
        Route::get('/dashboard-triad-criteria', [DashboardTriadController::class, 'CriteriaBreakdown']);
        Route::get('/dashboard-triad-evaluators', [DashboardTriadController::class, 'EvaluatorBreakdown']);
    });

    // Shared endpoints — viewing evaluations/timeline (dashboards, monitoring, and managers)
    Route::middleware('access:web_dashboard,web_report_monitoring,web_managers')->group(function () {
        Route::get('/dashboard/cards', [DashboardControllerMain::class, 'dashbaordCard']);
        Route::get('/ticket/view/{id}', [ViewTicket::class, 'viewTicket'])->name('viewticket');
        Route::post('/ticket/{auditId}/acknowledge', [AcknowledgementController::class, 'store'])->name('ticket.acknowledge');
        Route::get('/export/evaluations', [ExportController::class, 'evaluations'])->name('export.evaluations');
        Route::get('/evaluations/{auditId}/timeline', [AnalyticsController::class, 'timeline'])->name('evaluations.timeline');
    });
    Route::middleware('access:web_dashboard,web_report_action_register,web_managers')->group(function () {
        Route::get('/dashboard-recon-cards', [DashboardReconController::class, 'CardCount']);
    });
    Route::middleware('access:web_dashboard,web_report_triad,web_managers')->group(function () {
        Route::get('/dashboard-triad-cards', [DashboardTriadController::class, 'CardCount']);
    });

    /* -----------------------------------------------------------------
     | Forms (web_forms)
     | ----------------------------------------------------------------*/
    Route::middleware('access:web_forms,web_managers')->group(function () {
        Route::get('/formbuilder', function () {
            return view('formbuilder');
        })->name('formbuilder');
        Route::get('/formbuilder/{id}', [FormBuilderController::class, 'show'])->name('formbuilder.show');
        Route::get('/viewforms', function () {
            return view('viewforms');
        })->name('viewforms');
        Route::post('/viewforms/form', [FormInsertController::class, 'createForm'])->name('viewforms.createForm');
        Route::get('/forms/data', [DisplayFormListController::class, 'displayFormList'])->name('forms.data');

        Route::get('/monitoringform', [UserListMonitoringPage::class, 'UserList'])->name('monitoringform');
        Route::post('/api/audits', [AuditController::class, 'store']);
    });

    /* -----------------------------------------------------------------
     | Evaluations report (web_report_monitoring)
     | ----------------------------------------------------------------*/
    Route::middleware('access:web_report_monitoring,web_managers')->group(function () {
        Route::get('/eval-individual', [UserListMonitoringPage::class, 'EvalIndiData']);
        Route::get('/load-blade', [EvalIndividual::class, 'userTicket']);
        Route::get('/evaluation/individual-recent', [EvalIndividual::class, 'recentTableAPI']);
        Route::get('/evaluation/individual-cause-issue', [EvalIndividual::class, 'cause_issue_count']);
        Route::get('/evaluation/individual-accountable-factor', [EvalIndividual::class, 'impact_factor_count']);
    });

    /* -----------------------------------------------------------------
     | Management Reports (web_managers)
     | ----------------------------------------------------------------*/
    Route::middleware('access:web_managers')->group(function () {
        Route::get('/lda-scorecard', [LdaScorecardController::class, 'index'])->name('lda-scorecard');
        Route::get('/reports/pending-acknowledgements', [ReportController::class, 'pendingAcknowledgements'])->name('reports.pending-acknowledgements');
        Route::get('/analytics/auditor-productivity', [AnalyticsController::class, 'auditorProductivity'])->name('analytics.auditor-productivity');
        Route::get('/analytics/root-cause', [AnalyticsController::class, 'rootCause'])->name('analytics.root-cause');
        Route::get('/analytics/audit-coverage', [AnalyticsController::class, 'auditCoverage'])->name('analytics.audit-coverage');
        Route::get('/reports/disputes', [DisputeController::class, 'index'])->name('reports.disputes');
        Route::post('/reports/disputes/{id}/resolve', [DisputeController::class, 'resolve'])->name('reports.disputes.resolve');
        Route::get('/evaluations/{auditId}/correct', [ScoreCorrectionController::class, 'edit'])->name('evaluations.correct');
        Route::post('/evaluations/{auditId}/correct', [ScoreCorrectionController::class, 'update'])->name('evaluations.correct.save');
    });

    /* -----------------------------------------------------------------
     | Action Register report (web_report_action_register)
     | ----------------------------------------------------------------*/
    Route::middleware('access:web_report_action_register,web_managers')->group(function () {
        Route::get('/recon-ticket', [ReconTiketController::class, 'index']);
        Route::get('/recon-overdue', [ReconTiketController::class, 'overdue'])->name('recon-overdue');
        Route::get('/recon-data', [ReconTiketController::class, 'displayTicket']);
        Route::get('/recon-filter-options', [ReconTiketController::class, 'filterOptions']);
        Route::get('/recon-ticket-view/{id}', [ReconTiketController::class, 'fullDetails']);
        Route::post('/recon-ticket-add-comment', [ReconTiketController::class, 'addCommentToTicket']);
        Route::get('/recon-view-comment/{id}', [ReconTiketController::class, 'viewComment']);
        Route::post('/recon/assignto/{id}', [ReconTiketController::class, 'insertAssignTo']);
        Route::post('/recon/status-change/{id}', [ReconTiketController::class, 'ChangeStatus']);
        Route::get('/export/recon', [ExportController::class, 'recon'])->name('export.recon');
        Route::get('/analytics/client-carrier-health', [AnalyticsController::class, 'clientCarrierHealth'])->name('analytics.client-carrier-health');
    });

    /* -----------------------------------------------------------------
     | Coaching report (web_report_coaching)
     | ----------------------------------------------------------------*/
    Route::middleware('access:web_report_coaching,web_managers')->group(function () {
        Route::get('/viewcoaching', [UserListMonitoringPage::class, 'CoachingFormData']);
        Route::post('/coaching', [CoachingController::class, 'store']);
        Route::get('/coaching-ticket', [CoachingTicket::class, 'index']);
        Route::get('/coaching-data', [CoachingTicket::class, 'displayTicket']);
        Route::get('/coaching-ticket-view/{id}', [CoachingTicket::class, 'fullDetails']);
        Route::get('/api/coaching-ticket', [CoachingFormController::class, 'coachingTicketInformation']);
    });

    /* -----------------------------------------------------------------
     | Triad report (web_report_triad)
     | ----------------------------------------------------------------*/
    Route::middleware('access:web_report_triad,web_managers')->group(function () {
        Route::get('/viewtriad', [UserListMonitoringPage::class, 'CoachingTriadData']);
        Route::get('/triad-ticket', [TriadTicket::class, 'index']);
        Route::get('/triad-data', [TriadTicket::class, 'displayTicket']);
        Route::get('/triad-ticket-view/{id}', [TriadTicket::class, 'fullDetails']);
        Route::get('/api/coaching-triad', [CoachingTriadController::class, 'coachingRef']);
        Route::get('/api/triad-ticket', [CoachingTriadController::class, 'triadTicketInformation']);
        Route::get('/export/triad', [ExportController::class, 'triad'])->name('export.triad');

        Route::prefix('triad')->group(function () {
            Route::post('/', [TriadItemController::class, 'store']);
            Route::get('/', [TriadItemController::class, 'index']);
            Route::get('/{reference}', [TriadItemController::class, 'show']);
            Route::put('/{reference}', [TriadItemController::class, 'update']);
        });
    });
});
/*
|--------------------------------------------------------------------------
| API Form fields for Extension
|--------------------------------------------------------------------------
*/

// Route::get('api/field/{name}', [DefaultFieldApi::class, 'index']);
// Route::get('api/forms/recon', [ReconFieldController::class, 'index']);
// Route::get('api/login/verify', [LoginVerifyController::class, 'validateMicrosoftToken']);
// Route::get('api/recon', function () {
//     return view('extension.recon');
// })->name('recon');

Route::get('api/login/verify', [LoginVerifyController::class, 'validateMicrosoftToken']);




Route::middleware(['ms.jwt'])->group(function () {
    Route::get('api/field/{name}', [DefaultFieldApi::class, 'index']);
    Route::get('api/forms/recon', [ReconFieldController::class, 'index']);
    
    // Route::get('api/forms/menu', function () {
    //     return view('extension.menu');
    // })->name('menu');

    

    Route::get('api/forms/selection', [UserListMonitoringPage::class, 'SelectionUserList']);
    // Route::get('api/forms/selection', function () {
    //     return view('extension.selection');
    // })->name('menu');
});