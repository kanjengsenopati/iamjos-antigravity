<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KeywordController;

// Health Check API — publik, tanpa autentikasi, rate limited
Route::get('v1/health', \App\Http\Controllers\Api\HealthCheckController::class)
    ->middleware('throttle:60,1')
    ->name('api.health');

// COUNTER R5 Statistics API — publik, rate limited (standar COUNTER untuk harvesters)
Route::prefix('v1/counter')->middleware('throttle:30,1')->group(function () {
    Route::get('tr/{journal}', [\App\Http\Controllers\Api\CounterR5Controller::class, 'titleReport'])
        ->name('api.counter.tr');
    Route::get('ir/{journal}', [\App\Http\Controllers\Api\CounterR5Controller::class, 'itemReport'])
        ->name('api.counter.ir');
});

Route::prefix('v1')->middleware('validate_api_key')->group(function () {});

// Internal API for Reviewers & Keywords — protected by API key
Route::middleware('validate_api_key')->group(function () {
    Route::get('journal/{journal}/reviewers', [\App\Http\Controllers\Api\ReviewerApiController::class, 'index'])->name('api.journal.reviewers');
    Route::get('keywords', [KeywordController::class, 'index'])->name('api.keywords');
});

