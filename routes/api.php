<?php

use App\Http\Controllers\Api\CataloguController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\PymentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DistanceController;
use App\Http\Controllers\GetController;
use App\Http\Controllers\LoginActivityController;
use App\Http\Controllers\OnboardController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PercentageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebsitePagesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [UserController::class, 'register']);

Route::post('/login', [UserController::class, 'login']);

Route::get('/verification/{id}', [UserController::class, 'verification']);
Route::post('/verified', [UserController::class, 'verifiedOtp']);

Route::get('/profile', [UserController::class, 'profile']);

Route::post('/resendOtp', [UserController::class, 'resendOtp']);

Route::post('/getOtp', [UserController::class, 'sendOtp']);
Route::post('/change-password', [UserController::class, 'changePassword']);

Route::get('/refreshToken', [UserController::class, 'refreshToken']);
Route::group(['middleware' => 'api'], function ($routes) {
    Route::get('/logout', [UserController::class, 'logout']);
    Route::post('/reset-password', [UserController::class, 'resetPassword']);
    Route::post('/profileUpdate', [UserController::class, 'profileUpdate']);
    Route::post('/profileUpdate/img', [UserController::class, 'updateProfileImg']);

    Route::get('/notification', [HomeController::class, 'markRead']);
    Route::get('/read-at/notification', [HomeController::class, 'readNotification']);
    // Route::post('/send-notification', [UserController::class, 'sendNotification']);
});

Route::middleware(['admin'])->group(function () {
    // category

    Route::get('single-category/{id}', [CategoryController::class, 'showSingleCategory']);
    Route::post('add-category', [CategoryController::class, 'addCategory']);
    Route::post('update-category/{id}', [CategoryController::class, 'updateCategory']);
    Route::get('delete-category/{id}', [CategoryController::class, 'deleteCategory']);

    // Package
    Route::get('show-package', [PackageController::class, 'showPackage']);
    Route::get('single-package/{id}', [PackageController::class, 'showSinglePackage']);
    Route::post('add-package', [PackageController::class, 'addPackage']);
    Route::post('update-package/{id}', [PackageController::class, 'updatePackage']);
    Route::get('delete-package/{id}', [PackageController::class, 'deletePackage']);

    // website pages
    Route::get('show-website-pages', [WebsitePagesController::class, 'showWebsitePages']);
    Route::get('show-single-pages/{id}', [WebsitePagesController::class, 'showSinglePages']);
    Route::post('add-website-pages', [WebsitePagesController::class, 'addWebsitePage']);
    Route::post('update-website-pages/{id}', [WebsitePagesController::class, 'updateWebsitePage']);
    Route::get('delete-website-pages/{id}', [WebsitePagesController::class, 'deleteWebsitePage']);

    // Onboard pages
    Route::post('add-onboard', [OnboardController::class, 'addOnboard']);
    // Route::post('update-onboard',[OnboardController::class,'updateOnboard']);
    Route::get('show-onboard', [OnboardController::class, 'showOnboard']);
    Route::get('show-single-onboard/{id}', [OnboardController::class, 'showSingleOnboard']);
    Route::get('delete-single-onboard/{id}', [OnboardController::class, 'deleteOnboard']);

    // login activity
    Route::get('login-activity', [LoginActivityController::class, 'loginActivity']);
    Route::get('sign-out-login/{id}', [LoginActivityController::class, 'signOutLoginActivity']);

    // get salon list
    Route::get('get-salon-list', [GetController::class, 'salonList']);
    // get salon list with pagination
    Route::get('get-salon-list/{id}', [GetController::class, 'singleSalon']);

    // Get Provider Request
    Route::get('get-provider-request', [GetController::class, 'getProviderRequest']);

    // update provider request
    Route::get('approve-provider-request/{id}', [GetController::class, 'approveProviderRequest']);
    Route::get('block-provider-request/{id}', [GetController::class, 'blockProviderRequest']);

    // provider block list
    Route::get('provider-block-list', [GetController::class, 'providerBlockList']);
    Route::get('unblock-provider', [GetController::class, 'unblockProvider']);
    // get User List
    Route::get('user-list', [GetController::class, 'userList']);
    // single user data
    Route::get('single-user/{id}', [GetController::class, 'singleUser']);

    // search
    // provider request search by name and id
    Route::get('search-provider-request/{name}/{id?}', [GetController::class, 'searchProviderRequest']);
    // provider list search name,email,phone
    // provider block list search by name and id
    // user list search by name email and phone
    // salon list search by name,email and phone

    // booking percentage
    Route::post('booking-percentage-set', [PercentageController::class, 'percentageSet']);

    // review
    Route::get('/deleteRating/{id}', [UserController::class, 'deleteServiceRating']);
    Route::get('/showRating', [UserController::class, 'showServiceRating']);
    Route::get('/editRating/{id}', [UserController::class, 'editServiceRating']);
});

Route::middleware(['provider'])->group(function () {
    // ======================Provider =======================//
    Route::get('show-category', [CategoryController::class, 'showCategory']);
    Route::post('/post/provider', [ProviderController::class, 'postProvider']);
    Route::get('/get/provider', [ProviderController::class, 'getProvider']);
    Route::get('/edit/provider/{id}', [ProviderController::class, 'editProvider']);
    Route::post('/update/provider', [ProviderController::class, 'updateProvider']);
    Route::get('/delet/provider/cover/photo', [ProviderController::class, 'deleteProviderCoverImg']);
    Route::post('/update/provider/cover/photo', [ProviderController::class, 'providerCoverPhotoUpdate']);
    Route::get('/delet/provider/gallary/photo', [ProviderController::class, 'deleteProviderGallary']);
    Route::post('/update/provider/gallary/photo', [ProviderController::class, 'providerGallaryPhotoUpdate']);
    Route::get('/delete/provider/{id}', [ProviderController::class, 'deleteProvider']);

    // ======================SERVICE =======================//

    Route::post('/post/service', [ProviderController::class, 'postService']);
    Route::get('/get/service', [ProviderController::class, 'getService']);
    Route::get('/edit/service/{id}', [ProviderController::class, 'serviceEdit']);
    Route::get('/update/service/', [ProviderController::class, 'serviceUpdate']);
    Route::post('/post/update/service', [ProviderController::class, 'updateService']);
    Route::post('/post/update/service/image', [ProviderController::class, 'updateServiceImage']);
    Route::post('/post/delete/service/image', [ProviderController::class, 'deleteServiceGallary']);
    Route::get('/delete/service/{id}', [ProviderController::class, 'serviceDelete']);
    Route::get('/provider/all/service/{id}', [ProviderController::class, 'providerAllService']);

    // ====================CATALOUG ==============================//

    Route::post('/post/catalouge', [CataloguController::class, 'postCataloug']);
    Route::get('/get/catalouge/{id}', [CataloguController::class, 'getCataloug']);
    Route::get('/get/singel/catalouge/{id}', [CataloguController::class, 'singleCataloug']);
    Route::post('/update/catalouge', [CataloguController::class, 'updateCatalouge']);
    Route::post('/update/catalouge/image', [CataloguController::class, 'updateCatalougeImg']);
    Route::post('/catalouge/image/delete', [CataloguController::class, 'deleteCatalougImg']);
    Route::get('/delete/catalouge/{id}', [CataloguController::class, 'deleteCatlouge']);

    // ================== Booking ========================//

    Route::get('/booking', [ProviderController::class, 'booking']);
    Route::get('/booking/request', [ProviderController::class, 'bookingRequest']);
    Route::get('/booking/details/{id}', [ProviderController::class, 'bookingDetails']);
    Route::post('/re/schedule/appoinment', [ProviderController::class, 're_shedule_appoinment']);

    Route::post('/booking/accept', [ProviderController::class, 'bookingAccept']);
    Route::get('/booking/delete/{id}', [ProviderController::class, 'decline']);
    Route::get('/approved/booking', [ProviderController::class, 'approvedBooking']);
    Route::get('/booking/history', [ProviderController::class, 'bookingHistory']);
    Route::get('/review/provider', [ProviderController::class, 'reviewProvider']);

    // ========================== EARNING =========================//

    Route::get('/month/income', [PymentController::class, 'MonthlyIncome']);
    Route::get('/week/income', [PymentController::class, 'WeeklyIncome']);
    Route::get('/year/income', [PymentController::class, 'Last7YearsIncome']);
});

Route::middleware(['user'])->group(function () {
    // category route
    Route::get('single-category/{id}', [CategoryController::class, 'showSingleCategory']);
    Route::get('show-category', [CategoryController::class, 'showCategory']);

    // user
    Route::post('/saveRating', [UserController::class, 'saveRating']);
    Route::post('/updateRating/{id}', [UserController::class, 'updateServiceRating']);

    // find Nearest Location
    Route::get('/find-nearest-location', [DistanceController::class, 'findNearestLocation']);

    // ==================== USER HOME PAGE   ============================//

    Route::get('/user/home', [HomeController::class, 'userHome']);
    Route::get('/category/details/{id}', [HomeController::class, 'categoryDetails']);
    Route::get('/salon/details/{id}', [HomeController::class, 'salounDetails']);

    Route::get('/salon/list/{id}', [HomeController::class, 'salounList']);
    Route::get('/salon/service/{id}', [HomeController::class, 'salounService']);
    Route::get('/service/details/{id}', [HomeController::class, 'serviceDetails']);
    Route::get('/salon/details/{id}', [HomeController::class, 'selonDetails']);
    Route::get('/catalog/{id}', [HomeController::class, 'catalouge']);
    Route::get('/catalog/details/{id}', [HomeController::class, 'catalougeDetails']);

    Route::get('/appoinments/booking/show/{id}', [HomeController::class, 'bookingAppoinment']);
    Route::post('/post/booking', [HomeController::class, 'postBooking']);
    Route::get('/booking/summary', [HomeController::class, 'bookingSummary']);
    Route::get('/provider/approval', [HomeController::class, 'providerApproval']);
    Route::get('/appoinments/{id}', [HomeController::class, 'appoinments']);
    Route::get('/appoinments/cancel/{id}', [HomeController::class, 'bookingCancel']);
    Route::post('/re-schdule', [HomeController::class, 'reSchedule']);
    Route::get('/booking/details/{id}', [HomeController::class, 'bookingDetails']);
    Route::get('/booking/details/{id}', [HomeController::class, 'bookingDetails']);
});

// ====================Appointment from dashboard ==============================//

Route::get('appointment-list', [GetController::class, 'getAppointmentList']);

Route::get('/search/category', [HomeController::class, 'searchCategory']);

// ========================= PYMENT METHOD ROUTE =======================//

// The route that the button calls to initialize payment
Route::post('/pay', [PymentController::class, 'initialize'])->name('paynow');
// The callback url after a payment
Route::get('/rave/callback', [PymentController::class, 'callback'])->name('callback');
