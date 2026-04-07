<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ReconActionItemController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\TriadItemController;
use App\Http\Controllers\Api\CoachingController;
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
});



