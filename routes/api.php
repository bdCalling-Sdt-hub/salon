<?php


use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OnboardController;
use App\Http\Controllers\PackageController;
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
});

Route::middleware(['provider'])->group(function (){
    //provider
});

Route::middleware(['client'])->group(function (){
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
