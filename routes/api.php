<?php

use App\Http\Controllers\Api\ProviderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
