<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FlutterwaveController;
use App\Http\Controllers\TrashController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
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
Route::get('/delete/{id}',[UserController::class,'deleteUser']);
Route::get('auth/google',[UserController::class,'loginWithGoogle']);
Route::post('auth/google/callback',[UserController::class,'callbackFromGoogle']);

Route::post('/saveRating',[UserController::class,'saveRating']);
Route::post('/updateRating/{id}',[UserController::class,'updateServiceRating']);
Route::get('/deleteRating/{id}',[UserController::class,'deleteServiceRating']);
Route::get('/showRating',[UserController::class,'showServiceRating']);
Route::get('/editRating/{id}',[UserController::class,'editServiceRating']);
Route::group(['middleware'=>'api'],function($routes){
    Route::get('/logout',[UserController::class,'logout']);
    Route::post('/reset-password',[UserController::class,'resetPassword']);
    Route::post('/profileUpdate',[UserController::class,'profileUpdate']);
    Route::post('/send-notification',[UserController::class,'sendNotification']);
  

});

Route::post('/pay', [FlutterwaveController::class, 'initialize']);
Route::get('/rave/callback', [FlutterwaveController::class, 'callback']);

Route::get('/all_user_with_trash',[TrashController::class,'all_user_with_trash']);
Route::get('/trash_user_list',[TrashController::class,'trash_user_list']);
Route::get('/trash_restore/{id}',[TrashController::class,'trash_restore']);
Route::get('/trash_permanent_delete/{id}',[TrashController::class,'trash_permanent_delete']);