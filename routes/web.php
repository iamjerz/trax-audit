<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\LoginController;
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
    // Dashboard QA Monitoring Page
    Route::get('/homepage', function () {
        return view('homepage');
    })->name('homepage');
    Route::get('/dashboard-qa', function () {
        return view('dashboard');
    });
    // Create Form Page
    Route::get('/formbuilder', function () {
        return view('formbuilder');
    })->name('formbuilder');
    Route::get('/formbuilder/{id}', [FormBuilderController::class, 'show'])
    ->name('formbuilder.show');
    // View Forms Page
    Route::get('/viewforms', function () {
        return view('viewforms');
    })->name('viewforms');
    // Create Form POST Route
    Route::post('/viewforms/form', [FormInsertController::class, 'createForm'])
    ->name('viewforms.createForm');
    // View Individual Eval
    Route::get('/eval-individual', [UserListMonitoringPage::class, 'EvalIndiData']);
    // View Ticket Page
    Route::get('/ticket/view/{id}', [ViewTicket::class, 'viewTicket'])->name('viewticket');
    // view Triad Trial Form 
    Route::get('/viewtriad', [UserListMonitoringPage::class, 'CoachingTriadData']);
    // // View for Triad Form
    // Route::get('/viewtriad', [TriadFormController::class, 'index']);
    // Viewe for Coaching Form
    Route::get('/viewcoaching', [UserListMonitoringPage::class, 'CoachingFormData']);

    
    // Users Page
    // Route::get('/users', function () {
    //     return view('users');
    // })->name('users');
    Route::get('/users', [UserListMonitoringPage::class, 'UserPageList']);

    // Monitoring Form Page
    Route::get('/monitoringform', [UserListMonitoringPage::class, 'UserList'])
    ->name('monitoringform');

    // Logout Route
    Route::post('/logout', function (Request $request) {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');

    // API (JSON)
    // Users Data API
    Route::get('/users/data', [UserController::class, 'usersCallApi'])
    ->name('users.data');
    // Forms Data API
    Route::get('/forms/data', [DisplayFormListController::class, 'displayFormList'])
    ->name('forms.data');
    // Audit Store API
    Route::post('/api/audits', [AuditController::class, 'store']);
    // Dashboard Cards API
    Route::get('/dashboard/cards', [DashboardControllerMain::class, 'dashbaordCard']);
    // Dashboard Recent Table Ticket API
    Route::get('/dashboard/recent-ticket', [DashboardControllerMain::class, 'dashboardRecentTableTicket']);
    // Impact Factor Ticket API
    Route::get('/dashboard/accountable-factor', [DashboardControllerMain::class, 'impact_factor_count']);
    // Cause Issue Table  API
    Route::get('/dashboard/cause-issue', [DashboardControllerMain::class, 'cause_issue_count']);
    // Root Cause Table  API
    Route::get('/dashboard/root-cause', [DashboardControllerMain::class, 'root_cause_count']);
    // Individual Eval API for Recent Ticket
    Route::get('/evaluation/individual-recent', [EvalIndividual::class, 'recentTableAPI']);
    // Individual Eval API for Cause Issue
    Route::get('/evaluation/individual-cause-issue', [EvalIndividual::class, 'cause_issue_count']);
    // Individual Eval API for Cause Issue
    Route::get('/evaluation/individual-accountable-factor', [EvalIndividual::class, 'impact_factor_count']);
    // Coaching Triad API
    Route::get('/api/coaching-triad', [CoachingTriadController::class, 'coachingRef']);
    // Triad Ticket Information API
    Route::get('/api/triad-ticket', [CoachingTriadController::class, 'triadTicketInformation']);
    // Coaching Ticket Information API
    Route::get('/api/coaching-ticket', [CoachingFormController::class, 'coachingTicketInformation']);
    
    // Extra Body for Page

    // Individual Eval
    Route::get('/load-blade', [EvalIndividual::class, 'userTicket']);
    // Web Base
    Route::prefix('triad')->group(function () {
        Route::post('/', [TriadItemController::class, 'store']);        // create/update
        Route::get('/', [TriadItemController::class, 'index']);         // list
        Route::get('/{reference}', [TriadItemController::class, 'show']); // get one
        Route::put('/{reference}', [TriadItemController::class, 'update']); // update
    });

    Route::post('/coaching', [CoachingController::class, 'store']);

    // For Recon
    Route::get('/recon-ticket', [ReconTiketController::class, 'index']);
    Route::get('/recon-data', [ReconTiketController::class, 'displayTicket']);
    Route::get('/recon-filter-options', [ReconTiketController::class, 'filterOptions']);
    Route::get('/recon-ticket-view/{id}', [ReconTiketController::class, 'fullDetails']);
    Route::post('/recon-ticket-add-comment', [ReconTiketController::class, 'addCommentToTicket']);
    Route::get('/recon-view-comment/{id}', [ReconTiketController::class, 'viewComment']);
    Route::post('/recon/assignto/{id}', [ReconTiketController::class, 'insertAssignTo']);
    Route::post('/recon/status-change/{id}', [ReconTiketController::class, 'ChangeStatus']);
    Route::get('/dashboard-recon', [DashboardReconController::class, 'index']);
    Route::get('/dashboard-recon-cards', [DashboardReconController::class, 'CardCount']);
    Route::get('/dashboard-recon-table-top10', [DashboardReconController::class, 'Top10Breakdown']);
    Route::get('/dashboard-recon-chart-clientcode', [DashboardReconController::class, 'TopClientsChart']);
    Route::get('/dashboard-recon-chart-carriercode', [DashboardReconController::class, 'TopCarriers']);

    // Ticket Triad

    Route::get('/triad-ticket', [TriadTicket::class, 'index']);
    Route::get('/triad-data', [TriadTicket::class, 'displayTicket']);
    Route::get('/triad-ticket-view/{id}', [TriadTicket::class, 'fullDetails']);

    // Coaching Triad

    Route::get('/coaching-ticket', [CoachingTicket::class, 'index']);
    Route::get('/coaching-data', [CoachingTicket::class, 'displayTicket']);
    Route::get('/coaching-ticket-view/{id}', [CoachingTicket::class, 'fullDetails']);



    Route::get('/check-email', [UserPageController::class, 'check']);
    Route::post('/insert-user', [UserPageController::class, 'store']);
    Route::get('/edit-user/{employeeid}', [UserPageController::class, 'index']);
    Route::put('/users/edit/{employeeid}', [UserPageController::class, 'updateUser'])->name('users.update');
    Route::put('/users/{employeeid}/access', [UserPageController::class, 'updateAccessOnly']);
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