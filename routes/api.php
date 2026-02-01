<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('validate_api_key')->group(function () {});

// Internal API for Reviewers
Route::get('journal/{journal}/reviewers', [\App\Http\Controllers\Api\ReviewerApiController::class, 'index'])->name('api.journal.reviewers');
