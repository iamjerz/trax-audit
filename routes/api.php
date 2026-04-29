<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReconActionItemController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\TriadItemController;
use App\Http\Controllers\Api\CoachingController;
use App\Http\Controllers\Api\CoachingFormController;
use App\Http\Controllers\Api\CoachingTriadController;
use App\Http\Controllers\Api\ComboController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\DropdownController;
use App\Http\Controllers\Api\QaMonitoringFormController;



Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::middleware(['ms.jwt'])->group(function () {
    Route::prefix('/recon')->group(function () {
        //Route::get('/', [ReconActionItemController::class, 'index']);
        //Route::get('/{id}', [ReconActionItemController::class, 'show']);
        Route::post('/', [ReconActionItemController::class, 'store']);
        //Route::put('/{id}', [ReconActionItemController::class, 'update']);
        //Route::delete('/{id}', [ReconActionItemController::class, 'destroy']);
        
    });

    Route::prefix('/qa-form')->group(function () {
        Route::post('/', [AuditController::class, 'store']);
        
    });

    Route::prefix('triad')->group(function () {
        Route::post('/', [TriadItemController::class, 'store']);        // create/update
        Route::get('/', [TriadItemController::class, 'index']);         // list
        Route::get('/{reference}', [TriadItemController::class, 'show']); // get one
        Route::put('/{reference}', [TriadItemController::class, 'update']); // update
    });
    

    Route::post('/coaching', [CoachingController::class, 'store']);

    // view form
    Route::get('/forms/coaching-ticket', [CoachingFormController::class, 'coachingTicketInformation']);
    Route::get('/forms/triad-ticket', [CoachingTriadController::class, 'triadTicketInformation']);
    Route::get('/selection/ticket', [CoachingTriadController::class, 'coachingRef']);
    Route::get('/dropdown/client-code', [ComboController::class, 'clientCode']);
    Route::get('/dropdown/carrier-code', [ComboController::class, 'carrierCode']);



    Route::post('/forms/menu/', [MenuController::class, 'index']);

    // TEST ONLY
    Route::get('/client-codes', [DropdownController::class, 'clientCodes']);
    Route::get('/carrier-codes', [DropdownController::class, 'carrierCodes']);
    Route::get('/carrier-codes-nd', [DropdownController::class, 'carrierCodesNo']);
    Route::get('/audit-conditions', [DropdownController::class, 'auditConditions']);


    Route::post('/forms/qa', [QaMonitoringFormController::class, 'index']);
});



