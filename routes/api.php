<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\BpdController;
use App\Http\Controllers\Api\V1\HomeController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::prefix('v1')->middleware('validate_api_key')->group(function () {
    Route::get('home', [HomeController::class, 'index'])->name('home');
    Route::get('bpd', [BpdController::class, 'index'])->name('bpd.index');
    Route::middleware('auth:api')->group(function () {
        // protected routes...
    });
});
