<?php

use App\Http\Controllers\Api\CataloguController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\PymentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistanceController;
use App\Http\Controllers\EarningsController;
use App\Http\Controllers\FlutterwaveController;
use App\Http\Controllers\GetController;
use App\Http\Controllers\LoginActivityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OnboardController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PercentageController;
use App\Http\Controllers\SocialLoginController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\TrashController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebsitePagesController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('social-login', [UserController::class, 'socialLogin']);

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

    Route::get('my-plan', [PackageController::class, 'myPlan']);
});

// website pages
Route::post('add-website-pages', [WebsitePagesController::class, 'addWebsitePage']);
Route::post('update-website-pages/{id}', [WebsitePagesController::class, 'updateWebsitePage']);
Route::get('delete-website-pages/{id}', [WebsitePagesController::class, 'deleteWebsitePage']);

Route::middleware(['admin'])->group(function () {
    Route::post('add-category', [CategoryController::class, 'addCategory']);
    Route::post('update-category/{id}', [CategoryController::class, 'updateCategory']);
    Route::get('delete-category/{id}', [CategoryController::class, 'deleteCategory']);

    // ======================dashboard ==============================//
    Route::get('booking-complete', [DashboardController::class, 'bookingComplete']);
    Route::get('booking-cancel', [DashboardController::class, 'bookingCancel']);
    Route::get('booking-pending', [DashboardController::class, 'bookingPending']);

    Route::get('appointment-list', [GetController::class, 'getAppointmentList']);
    Route::get('appointment-list/{id}', [GetController::class, 'appointmentListbyId']);

    // ======================Earnings ==============================//
    Route::get('payment-history-provider', [GetController::class, 'paymentHistory']);
    Route::get('payment-history-provider/{id}', [GetController::class, 'paymentHistoryById']);
    Route::get('payment-history-user', [GetController::class, 'paymentHistoryUser']);
    Route::get('payment-history-user/{id}', [GetController::class, 'paymentHistoryByIdUser']);

    // ======================Package ==============================//

    Route::post('add-package', [PackageController::class, 'addPackage']);
    Route::post('update-package/{id}', [PackageController::class, 'updatePackage']);
    Route::get('delete-package/{id}', [PackageController::class, 'deletePackage']);

    // ======================Category ==============================//

    Route::get('single-category/{id}', [CategoryController::class, 'showSingleCategory']);
    Route::post('add-category', [CategoryController::class, 'addCategory']);
    Route::post('update-category/{id}', [CategoryController::class, 'updateCategory']);
    Route::get('delete-category/{id}', [CategoryController::class, 'deleteCategory']);

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
    Route::get('cancel-provider-request/{id}', [GetController::class, 'cancelProviderRequest']);
    Route::get('block-provider-request/{id}', [GetController::class, 'blockProviderRequest']);

    // provider block list
    Route::get('provider-block-list', [GetController::class, 'providerBlockList']);
    Route::get('unblock-provider/{id}', [GetController::class, 'unblockProvider']);
    // get User List
    Route::get('user-list', [GetController::class, 'userList']);
    // provider list
    Route::get('provider-list', [GetController::class, 'providerList']);
    // single user data
    Route::get('single-user/{id}', [GetController::class, 'singleUser']);

    // search
    // provider request search by name and id
    Route::get('search-provider-request', [GetController::class, 'searchProviderRequest']);

    Route::get('delete-user/{id}', [GetController::class, 'deleteUser']);
    // provider list search name,email,phone

    Route::get('delete-user/{id}', [GetController::class, 'deleteUser']);
    // provider list search name,email,phone

    Route::get('search-provider/{name?}', [GetController::class, 'searchProvider']);
    // provider block list search by name and id
    Route::get('provider-block-list-search/{name?}', [GetController::class, 'searchProviderBlock']);
    // user list search by name email and phone
    Route::get('search-user/{name?}', [GetController::class, 'searchUser']);

    Route::get('salon-search/{name?}', [GetController::class, 'searchSalon']);

    // booking percentage
    Route::post('booking-percentage-set', [PercentageController::class, 'percentageSet']);
    Route::get('booking-percentage-set', [PercentageController::class, 'percentageGet']);
    Route::post('booking-percentage-update', [PercentageController::class, 'percentageUpdate']);

    // review
    Route::get('/deleteRating/{id}', [UserController::class, 'deleteServiceRating']);
    Route::get('/showRating', [UserController::class, 'showServiceRating']);
    Route::get('/editRating/{id}', [UserController::class, 'editServiceRating']);
    // review
    Route::get('/deleteRating/{id}', [UserController::class, 'deleteServiceRating']);
    Route::get('/showRating', [UserController::class, 'showServiceRating']);
    Route::get('/editRating/{id}', [UserController::class, 'editServiceRating']);

    // notification
    Route::get('/admin-notification', [NotificationController::class, 'adminNotification']);
    Route::get('/admin/read_at/notification', [NotificationController::class, 'adminReadAtNotification']);

    // Review
    Route::get('review', [GetController::class, 'getReviews']);
    Route::get('review-by-id/{id}', [GetController::class, 'getReviewsByProviderId']);
    // Route::get('review-average-rating/{id}',[GetController::class,'averageReviewRating']);
    Route::get('review-average-rating/{id}', [GetController::class, 'test']);

    // ====================Trash ==============================//

    Route::get('trash-user', [TrashController::class, 'trashUser']);
    Route::get('trash-restore/{id}', [TrashController::class, 'trashRestore']);
});

Route::middleware(['payment.auth'])->group(function () {
    // package
    Route::get('show-package', [PackageController::class, 'showPackage']);
    Route::get('package-renew/{id}', [PackageController::class, 'packageRenew']);

    // ======================Provider =======================//
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

    Route::get('/booking/request', [ProviderController::class, 'bookingRequest']);
    Route::get('/booking/details/provider/{id}', [ProviderController::class, 'bookingDetails']);
    Route::post('/re/schedule/appoinment', [ProviderController::class, 're_shedule_appoinment']);

    // Route::post('/booking/accept', [ProviderController::class, 'bookingAccept']);
    Route::get('/booking/delete/{id}', [ProviderController::class, 'decline']);
    Route::get('/approved/booking', [ProviderController::class, 'approvedBooking']);
    Route::get('/booking/history', [ProviderController::class, 'bookingHistory']);
    Route::get('/provider/review', [ProviderController::class, 'reviewProvider']);
});

Route::middleware(['provider'])->group(function () {
    // The route that the button calls to initialize payment
    Route::post('/pay/{id}', [FlutterwaveController::class, 'initialize'])->name('paynow');
    Route::post('payment-success', [FlutterwaveController::class, 'paymentSuccess']);

    // ========================== EARNING =========================//

    Route::get('/month/income', [PymentController::class, 'MonthlyIncome']);
    Route::get('/week/income', [PymentController::class, 'WeeklyIncome']);
    Route::get('/year/income', [PymentController::class, 'Last7YearsIncome']);
    Route::get('/booking', [ProviderController::class, 'booking']);

    // ======================= NOTIFICATION =====================//

    Route::get('salon/notification', [ProviderController::class, 'markRead']);
    Route::get('/send/booking/request/salon', [ProviderController::class, 'new_booking_request']);
    Route::get('/read-at/notification/{id}', [ProviderController::class, 'readAtNotification']);
});

Route::middleware(['user'])->group(function () {
    // booking provider

    Route::post('post-booking', [GetController::class, 'postBooking']);
    Route::get('get-booking', [GetController::class, 'getBooking']);

    // user booking - payment
    Route::post('/user-pay/{id}', [FlutterwaveController::class, 'userPayment']);
    Route::post('user-payment-success', [FlutterwaveController::class, 'UserpaymentSuccess']);

    // filter
    Route::get('user-filter/{category}/{rating}/{distance}', [DistanceController::class, 'filterOriginal']);

    // salon search in user home
    Route::get('salon-search-home/{salon?}', [DistanceController::class, 'searchProvidersBySalon']);
    // test
    Route::post('add-rev', [TestController::class, 'saveRev']);
    // category route
    Route::get('single-category/{id}', [CategoryController::class, 'showSingleCategory']);
    // user
    Route::post('/saveRating', [UserController::class, 'saveRating']);
    Route::post('/updateRating/{id}', [UserController::class, 'updateServiceRating']);

    Route::get('/find-nearest-location', [DistanceController::class, 'findNearestLocation']);

    // find nearest location by lat long
    Route::get('/find-nearest-location/{lat}/{long}/', [DistanceController::class, 'findNearestLocationByLatLong']);

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
    Route::get('/appoinments', [HomeController::class, 'appoinments']);
    Route::get('/appoinments/cancel/{id}', [HomeController::class, 'bookingCancel']);
    Route::post('/re-schdule', [HomeController::class, 'reSchedule']);
    Route::get('/booking/details/{id}', [HomeController::class, 'bookingDetails']);
    Route::get('/appointment/history', [HomeController::class, 'appoinmentHistory']);
    Route::get('/user/booking/reequest', [HomeController::class, 'userBookingRequest']);
    Route::get('/near/by/provider', [HomeController::class, 'nearbyProviders']);

    // appointment booking
    Route::get('/appointment-booking/{id}', [GetController::class, 'appointmentBooking']);

    // ==================================== NOTIFICATION ===============================//

    Route::get('/user/notification', [HomeController::class, 'markRead']);
    Route::get('/user/booking/accept/notification', [HomeController::class, 'user_booking_accept_notification']);
    Route::get('/read-at/user/notification/{id}', [HomeController::class, 'readAtNotification']);
});

Route::get('/filter-nearest-salon/{lat}/{long}', [DistanceController::class, 'findNearestSalon']);

Route::get('earnings', [EarningsController::class, 'Earnings']);

Route::get('/search/category', [HomeController::class, 'searchCategory']);

// get booking history
Route::get('booking-history', [GetController::class, 'bookingHistory']);

// route for user and provider
// Route::middleware(['both'])->group(function () {
//
// }

// filter
Route::get('filter/{name?}', [GetController::class, 'filter']);

// both is work for user and provider
Route::middleware(['user.provider'])->group(function () {
    Route::get('/service/details/{id}', [HomeController::class, 'serviceDetails']);
    Route::get('relation-filter', [TestController::class, 'relationFilter']);
    Route::get('get-reviews', [TestController::class, 'getReviews']);
});

// The callback url after a payment
Route::get('/rave/callback', [FlutterwaveController::class, 'callback'])->name('callback');
// user callback

Route::get('/rave/callback', [FlutterwaveController::class, 'userCallback'])->name('user.callback');

Route::middleware(['user.admin.provider'])->group(function () {
    Route::post('/booking/accept', [ProviderController::class, 'bookingAccept']);
    Route::get('category-search/{name?}', [CategoryController::class, 'categorySearch']);
    // category
    Route::get('show-category', [CategoryController::class, 'showCategory']);
    // website pages
    Route::get('show-website-pages', [WebsitePagesController::class, 'showWebsitePages']);
    Route::get('show-single-pages/{id}', [WebsitePagesController::class, 'showSinglePages']);
});

Route::middleware(['admin.provider'])->group(function () {
    // single-catagory
    Route::get('single-category/{id}', [CategoryController::class, 'showSingleCategory']);
    // package
    Route::get('show-package', [PackageController::class, 'showPackage']);
    Route::get('single-package/{id}', [PackageController::class, 'showSinglePackage']);
});
