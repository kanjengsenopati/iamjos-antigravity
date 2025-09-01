<?php

use App\Models\MeetingRoomInfo;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BpdController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\ArticleController;
use App\Http\Controllers\Admin\BenefitController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\HomeAdsController;
use App\Http\Controllers\Admin\ContactUsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\TranslateController;
use App\Http\Controllers\Admin\BookingInaController;
use App\Http\Controllers\Admin\HomeMemberController;
use App\Http\Controllers\Admin\HomeSectorController;
use App\Http\Controllers\Admin\HomeSliderController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\HomePartnerController;
use App\Http\Controllers\Admin\MediaCornerController;
use App\Http\Controllers\Admin\MeetingInfoController;
use App\Http\Controllers\Admin\MeetingRoomController;
use App\Http\Controllers\Admin\HotelBookingController;
use App\Http\Controllers\Admin\OrganizationController;
use App\Http\Controllers\Admin\AboutUsHistoryController;
use App\Http\Controllers\Admin\ForgotPasswordController;
use App\Http\Controllers\Admin\BppOrganizationController;
use App\Http\Controllers\Admin\HonoraryCouncilController;
use App\Http\Controllers\Admin\MeetingRoomInfoController;
use App\Http\Controllers\Admin\MeetingRoomTypeController;
use App\Http\Controllers\Admin\HomeDocumentationController;
use App\Http\Controllers\Admin\AboutUsInformationController;
use App\Http\Controllers\Admin\ApplicationSettingController;
use App\Http\Controllers\Admin\DirectionCommitmentController;
use App\Http\Controllers\Admin\RegionalCoordinatorController;

// ---------- Public / Auth ----------
Route::get('/', [AuthController::class, 'index']); // gunakan halaman login sebagai root
Route::get('/login', [AuthController::class, 'index'])->name('login');

Route::get('/forgot-password', [ForgotPasswordController::class, 'index'])->name('forgot-password');
Route::post('/forgot-password', [ForgotPasswordController::class, 'post'])->name('success-forgot-password');
Route::get('/change-password', [ForgotPasswordController::class, 'changePassword'])->name('change-password');
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword'])->name('reset-password');

Route::post('/login', [AuthController::class, 'authenticate'])->name('authenticate');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::resource('admin', AdminController::class);
Route::resource('permission', PermissionController::class);
Route::resource('role', RoleController::class);

// ---------- Protected ----------
Route::group(['middleware' => ['auth']], function () {
    // Profile
    Route::get('edit-profile-admin', [AdminController::class, 'editProfile'])
        ->name('profile-admin.edit')->withoutMiddleware('permission:admin');
    Route::put('edit-profile-admin', [AdminController::class, 'updateProfile'])
        ->name('profile-admin.update')->withoutMiddleware('permission:admin');

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    // routes/web.php
    Route::post('/home-partner/reorder', [HomePartnerController::class, 'reorder'])
        ->name('home-partner.reorder');

    // Home content
    Route::resource('home-slider', HomeSliderController::class);
    Route::resource('home-member', HomeMemberController::class);
    Route::resource('home-sector', HomeSectorController::class);
    Route::resource('home-ads', HomeAdsController::class);
    Route::resource('home-documentation', HomeDocumentationController::class);
    Route::resource('home-partner', HomePartnerController::class);
    Route::resource('booking-ina', BookingInaController::class);
    Route::resource('hotel-booking', HotelBookingController::class);

    // Article & Media Corner
    Route::resource('article', ArticleController::class);
    Route::patch('article/{article}/toggle-status', [ArticleController::class, 'toggleStatus'])->name('article.toggle-status');

    Route::patch('media-corner/{mediaCorner}/toggle-status', [MediaCornerController::class, 'toggleStatus'])->name('media-corner.toggle-status');
    Route::resource('media-corner', MediaCornerController::class, ['only' => ['index', 'destroy']]);

    // Event
    Route::resource('event', EventController::class, ['only' => ['index', 'destroy']]);
    Route::patch('event/{event}/toggle-status', [EventController::class, 'toggleStatus'])->name('event.toggle-status');
    Route::post('event/sync', [EventController::class, 'sync'])->name('event.sync');

    // BPD
    Route::get('/bpd', [BpdController::class, 'index'])->name('bpd.index');
    Route::post('/bpd/refresh', [BpdController::class, 'refresh'])->name('bpd.refresh');

    // About Us + Organization
    Route::resource('aboutus-information', AboutUsInformationController::class, ['only' => ['index', 'store']]);
    Route::resource('aboutus-history', AboutUsHistoryController::class, ['only' => ['index', 'store']]);
    Route::resource('direction-commitment', DirectionCommitmentController::class);
    Route::resource('honorary-council', HonoraryCouncilController::class);
    Route::resource('regional-coordinator', RegionalCoordinatorController::class);
    Route::resource('organization', OrganizationController::class);
    Route::resource('benefit', BenefitController::class);
    Route::resource('bpp-organization', BppOrganizationController::class);

    // Organization positions import/export
    Route::get('organization/export/positions', [OrganizationController::class, 'exportPositions'])->name('organization.export.positions');
    Route::post('organization/import/positions', [OrganizationController::class, 'importPositions'])->name('organization.import.positions');
    Route::get('organization/template/positions', [OrganizationController::class, 'downloadPositionTemplate'])->name('organization.template.positions');

    // Organization members import/export
    Route::get('organization/export/members', [OrganizationController::class, 'exportMembers'])->name('organization.export.members');
    Route::post('organization/import/members', [OrganizationController::class, 'importMembers'])->name('organization.import.members');
    Route::get('organization/template/members', [OrganizationController::class, 'downloadMemberTemplate'])->name('organization.template.members');

    // Member + Contact Us (+ Excel)
    Route::resource('member', MemberController::class, ['except' => ['show']]);
    Route::get('member/export/excel', [MemberController::class, 'exportMembers'])->name('member.export.excel');
    Route::post('member/import/excel', [MemberController::class, 'importMembers'])->name('member.import.excel');
    Route::get('member/template/excel', [MemberController::class, 'downloadMemberTemplate'])->name('member.template.excel');

    // BPP Organization Routes
    Route::resource('bpp-organization', BppOrganizationController::class);

    // Excel Import/Export for BPP Positions
    Route::get('bpp-organization/export/positions', [BppOrganizationController::class, 'exportPositions'])->name('bpp-organization.export.positions');
    Route::post('bpp-organization/import/positions', [BppOrganizationController::class, 'importPositions'])->name('bpp-organization.import.positions');
    Route::get('bpp-organization/template/positions', [BppOrganizationController::class, 'downloadPositionTemplate'])->name('bpp-organization.template.positions');

    // Excel Import/Export for BPP Members
    Route::get('bpp-organization/export/members', [BppOrganizationController::class, 'exportMembers'])->name('bpp-organization.export.members');
    Route::post('bpp-organization/import/members', [BppOrganizationController::class, 'importMembers'])->name('bpp-organization.import.members');
    Route::get('bpp-organization/template/members', [BppOrganizationController::class, 'downloadMemberTemplate'])->name('bpp-organization.template.members');

    Route::resource('contact', ContactController::class, ['except' => ['show']]);
    Route::resource('contact-us', ContactUsController::class, ['except' => ['show']]);

    Route::resource('meeting-room-info', MeetingRoomInfoController::class);
    Route::resource('meeting-room-type', MeetingRoomTypeController::class);
    // --------- Meeting Room (dari main) ---------
    Route::resource('meeting-room', MeetingRoomController::class);
    Route::post('meeting-room/sync', [MeetingRoomController::class, 'sync'])->name('meeting-room.sync');
    Route::get('meeting-room/regencies/{province}', [MeetingRoomController::class, 'getRegencies'])->name('meeting-room.regencies');
    Route::get('meeting-room-filter-data', [MeetingRoomController::class, 'getFilterData'])->name('meeting-room.filter-data');
    Route::get('meeting-room-cities/{province}', [MeetingRoomController::class, 'getCitiesByProvince'])->name('meeting-room.cities');
    Route::post('meeting-room/{meetingRoom}/gallery/upload', [MeetingRoomController::class, 'uploadGallery'])->name('meeting-room.gallery.upload');
    Route::delete('meeting-room/{meetingRoom}/gallery/{gallery}', [MeetingRoomController::class, 'deleteGallery'])->name('meeting-room.gallery.delete');
    Route::get('meeting-room-types', [MeetingRoomController::class, 'getMeetingRoomTypes'])->name('meeting-room.types');
    Route::post('meeting-room/{meetingRoom}/room/{room}/layout', [MeetingRoomController::class, 'storeLayout'])->name('meeting-room.layout.store');
    Route::put('meeting-room/{meetingRoom}/room/{room}/layout/{layout}', [MeetingRoomController::class, 'updateLayout'])->name('meeting-room.layout.update');
    Route::delete('meeting-room/{meetingRoom}/room/{room}/layout/{layout}', [MeetingRoomController::class, 'deleteLayout'])->name('meeting-room.layout.delete');

    // Rooms under a venue
    Route::prefix('venue/{venue}')->group(function () {
        Route::get('rooms', [RoomController::class, 'index'])->name('venue.rooms.index');
        Route::get('rooms/create', [RoomController::class, 'create'])->name('venue.rooms.create');
        Route::post('rooms', [RoomController::class, 'store'])->name('venue.rooms.store');
        Route::get('rooms/{room}/edit', [RoomController::class, 'edit'])->name('venue.rooms.edit');
        Route::put('rooms/{room}', [RoomController::class, 'update'])->name('venue.rooms.update');
        Route::delete('rooms/{room}', [RoomController::class, 'destroy'])->name('venue.rooms.destroy');
    });

    // --------- Application Settings (dari development) ---------
    Route::get('application-setting', [ApplicationSettingController::class, 'index'])->name('application-setting.index');
    Route::post('application-setting/backup', [ApplicationSettingController::class, 'backupDatabase'])->name('application-setting.backup');
    Route::get('application-setting/system-info', [ApplicationSettingController::class, 'getSystemInfo'])->name('application-setting.system-info');
    Route::get('application-setting/database-info', [ApplicationSettingController::class, 'getDatabaseInfo'])->name('application-setting.database-info');
    Route::post('application-setting/upload-ad-art', [ApplicationSettingController::class, 'uploadAdArt'])->name('application-setting.upload-ad-art');
    Route::get('application-setting/download-ad-art', [ApplicationSettingController::class, 'downloadAdArt'])->name('application-setting.download-ad-art');
    Route::delete('application-setting/delete-ad-art', [ApplicationSettingController::class, 'deleteAdArt'])->name('application-setting.delete-ad-art');
});

// ---------- Translate (public) ----------
Route::get('translate', [TranslateController::class, 'index'])->name('translate');
Route::post('translate_post', [TranslateController::class, 'translatePost'])->name('translate_post');
