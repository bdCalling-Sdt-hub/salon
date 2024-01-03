<?php

use App\Http\Controllers\Api\CataloguController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\PymentController;
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

// ================== Booking ========================//

Route::post('/post/booking', [ProviderController::class, 'postBooking']);
Route::get('/get/booking', [ProviderController::class, 'getBooking']);
Route::get('/edit/booking/{id}', [ProviderController::class, 'editBooking']);
Route::post('/update/booking', [ProviderController::class, 'updateBooking']);
Route::post('/update/status', [ProviderController::class, 'updateStatus']);
Route::get('/booking/delete/{id}', [ProviderController::class, 'cancelBooking']);

// ==================== CATEOGORY ============================//

Route::get('/get/category', [ProviderController::class, 'category']);

// ====================CATALOUG ==============================//

Route::post('/post/catalouge', [CataloguController::class, 'postCataloug']);
Route::get('/get/catalouge', [CataloguController::class, 'getCataloug']);
Route::get('/get/singel/catalouge/{id}', [CataloguController::class, 'singleCataloug']);
Route::post('/update/catalouge', [CataloguController::class, 'updateCatalouge']);
Route::post('/update/catalouge/image', [CataloguController::class, 'updateCatalougeImg']);
Route::post('/catalouge/image/delete', [CataloguController::class, 'deleteCatalougImg']);
Route::get('/delete/catalouge/{id}', [PaymentController::class, 'deleteCatlouge']);

// ================================ PYMENT METHOD ===========================//

// The route that the button calls to initialize payment
Route::post('/pay', [PymentController::class, 'initialize'])->name('paynow');
// The callback url after a payment
Route::get('/rave/callback', [PymentController::class, 'callback'])->name('callback');
