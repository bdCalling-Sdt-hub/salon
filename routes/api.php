<?php


use App\Http\Controllers\CategoryController;
use App\Http\Controllers\GetController;
use App\Http\Controllers\LoginActivityController;
use App\Http\Controllers\OnboardController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\PercentageController;
use App\Http\Controllers\TestController;
use App\Http\Controllers\WebsitePagesController;
use App\Http\Controllers\Api\ProviderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
/*
 * |--------------------------------------------------------------------------
 * | API Routes
 * |--------------------------------------------------------------------------
 * |
 * | Here is where you can register API routes for your application. These
 * | routes are loaded by the RouteServiceProvider and all of them will
 * | be assigned to the "api" middleware group. Make something great!
 * |
 */


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register',[UserController::class,'register']);

Route::post('/login',[UserController::class,'login']);

Route::get('/verification/{id}',[UserController::class,'verification']);
Route::post('/verified',[UserController::class,'verifiedOtp']);

Route::get('/profile',[UserController::class,'profile']);

Route::post('/resendOtp',[UserController::class,'resendOtp']);

Route::post('/getOtp',[UserController::class,'sendOtp']);

Route::get('/refreshToken',[UserController::class,'refreshToken']);

Route::group(['middleware'=>'api'],function($routes){
    Route::get('/logout',[UserController::class,'logout']);
    Route::post('/reset-password',[UserController::class,'resetPassword']);
    Route::post('/profileUpdate',[UserController::class,'profileUpdate']);
});


Route::middleware(['admin'])->group(function (){
    //category
    Route::get('show-category', [CategoryController::class,'showCategory']);
    Route::get('single-category/{id}', [CategoryController::class,'showSingleCategory']);
    Route::post('add-category', [CategoryController::class,'addCategory']);
    Route::post('update-category/{id}', [CategoryController::class,'updateCategory']);
    Route::get('delete-category/{id}', [CategoryController::class,'deleteCategory']);

//Package
    Route::get('show-package', [PackageController::class,'showPackage']);
    Route::get('single-package/{id}', [PackageController::class,'showSinglePackage']);
    Route::post('add-package', [PackageController::class,'addPackage']);
    Route::post('update-package/{id}', [PackageController::class,'updatePackage']);
    Route::get('delete-package/{id}', [PackageController::class,'deletePackage']);

//website pages
    Route::get('show-website-pages',[WebsitePagesController::class,'showWebsitePages']);
    Route::get('show-single-pages/{id}',[WebsitePagesController::class,'showSinglePages']);
    Route::post('add-website-pages',[WebsitePagesController::class,'addWebsitePage']);
    Route::post('update-website-pages/{id}',[WebsitePagesController::class,'updateWebsitePage']);
    Route::get('delete-website-pages/{id}',[WebsitePagesController::class,'deleteWebsitePage']);

//Onboard pages
    Route::post('add-onboard',[OnboardController::class,'addOnboard']);
//Route::post('update-onboard',[OnboardController::class,'updateOnboard']);
    Route::get('show-onboard',[OnboardController::class,'showOnboard']);
    Route::get('show-single-onboard/{id}',[OnboardController::class,'showSingleOnboard']);
    Route::get('delete-single-onboard/{id}',[OnboardController::class,'deleteOnboard']);

    // login activity
    Route::get('login-activity',[LoginActivityController::class,'loginActivity']);
    Route::get('sign-out-login/{id}',[LoginActivityController::class,'signOutLoginActivity']);

    //get salon list
    Route::get('get-salon-list',[GetController::class,'salonList']);
    //get salon list with pagination
    Route::get('get-salon-list/{id}',[GetController::class,'singleSalon']);

    //Get Provider Request
    Route::get('get-provider-request',[GetController::class,'getProviderRequest']);

    //update provider request
    Route::get('approve-provider-request/{id}',[GetController::class,'approveProviderRequest']);
    Route::get('block-provider-request/{id}',[GetController::class,'blockProviderRequest']);

    //provider block list
    Route::get('provider-block-list',[GetController::class,'providerBlockList']);
    Route::get('unblock-provider',[GetController::class,'unblockProvider']);
    //get User List
    Route::get('user-list',[GetController::class,'userList']);
    //single user data
    Route::get('single-user/{id}',[GetController::class,'singleUser']);

    //search
    //provider request search by name and id
    Route::get('search-provider-request/{name}/{id?}',[GetController::class,'searchProviderRequest']);
    //provider list search name,email,phone
    //provider block list search by name and id
    //user list search by name email and phone
    //salon list search by name,email and phone

    //booking percentage
    Route::post('booking-percentage-set',[PercentageController::class,'percentageSet']);
});

Route::middleware(['provider'])->group(function (){
    //provider
});

Route::middleware(['user'])->group(function (){
    //user
});

Route::post('/post/provider', [ProviderController::class, 'postProvider']);
Route::get('/get/provider', [ProviderController::class, 'getProvider']);
Route::post('/post/service', [ProviderController::class, 'postService']);
Route::get('/get/service', [ProviderController::class, 'getService']);

// ================== Booking ========================//

Route::post('/post/booking', [ProviderController::class, 'postBooking']);
Route::get('/get/booking', [ProviderController::class, 'getBooking']);
Route::get('/edit/booking/{id}', [ProviderController::class, 'editBooking']);
Route::post('/update/booking', [ProviderController::class, 'updateBooking']);
Route::post('/update/status', [ProviderController::class, 'updateStatus']);
Route::get('/booking/delete/{id}', [ProviderController::class, 'deletProvider']);
