<?php

use App\Http\Controllers\Api\V1\AboutUsController;
use App\Http\Controllers\Api\V1\AppInformationController;
use App\Http\Controllers\Api\V1\HomeAdsController as ApiHomeAdsController;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\BpdController;
use App\Http\Controllers\Api\V1\HomeController;
use App\Http\Controllers\Api\V1\EventController;
use App\Http\Controllers\Api\V1\ArticleController;
use App\Http\Controllers\Api\V1\BenefitController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\ContactUsController;
use App\Http\Controllers\Api\V1\MediaCornerController;
use App\Http\Controllers\Api\V1\MeetingRoomController;
use App\Http\Controllers\Api\V1\PartnerController;

Route::prefix('v1')->middleware('validate_api_key')->group(function () {
    Route::get('home', [HomeController::class, 'index'])->name('home');

    // Home Ads Tracking - lightweight endpoints with rate limiting
    Route::middleware('ads_rate_limit')->group(function () {
        Route::post('home-ads/{id}/view', [ApiHomeAdsController::class, 'incrementView'])->name('home-ads.view');
        Route::post('home-ads/{id}/click', [ApiHomeAdsController::class, 'incrementClick'])->name('home-ads.click');
    });
    Route::get('home-ads/{id}/statistics', [ApiHomeAdsController::class, 'getStatistics'])->name('home-ads.statistics');

    Route::get('bpd', [BpdController::class, 'index'])->name('bpd.index');
    Route::get('meeting-room', [MeetingRoomController::class, 'index'])->name('meeting-room.index');
    Route::get('meeting-room/filter-capacity', [MeetingRoomController::class, 'filterCapacity'])->name('meeting-room.filter-capacity');
    Route::get('meeting-room/{id}', [MeetingRoomController::class, 'show'])->name('meeting-room.show');
    Route::get('province', [MeetingRoomController::class, 'province'])->name('province.index');
    Route::get('regency', [MeetingRoomController::class, 'regency'])->name('regency.index');
    Route::get('media-corner', [MediaCornerController::class, 'index'])->name('media-corner.index');
    Route::get('benefit', [BenefitController::class, 'index'])->name('benefit.index');
    Route::get('contact', [ContactController::class, 'index'])->name('contact.index');
    Route::post('contact-us', [ContactUsController::class, 'store'])->name('contact-us.store');
    Route::get('event', [EventController::class, 'index'])->name('event.index');
    Route::get('event/{id}', [EventController::class, 'show'])->name('event.show');
    Route::apiResource('article', ArticleController::class)->only(['index', 'show']);
    Route::get('about-us', [AboutUsController::class, 'index'])->name('about-us.index');
    Route::get('app-information', [AppInformationController::class, 'index'])->name('app-information.index');
    Route::get('partner', [PartnerController::class, 'index'])->name('partner.index');
    Route::middleware('auth:api')->group(function () {
        // protected routes...
    });
});
