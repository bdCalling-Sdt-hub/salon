<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CatalougeRequest;
use App\Models\Catalogue;
use Illuminate\Http\Request;

class CataloguController extends Controller
{
    public function postCataloug(CatalougeRequest $request)
    {

        $cataloug_photo = time() . '.' . $request->catalougPhoto->extension();
        $image_path = 'images/' . $cataloug_photo; // Full path including directory

        $request->catalougPhoto->move(public_path('images'), $cataloug_photo);

// Now you can store $image_path in your database instead of just $cataloug_photo

        $auth_user = auth()->user()->id;
        $cataloug_photo = time() . '.' . $request->catalougPhoto->extension();
        $request->catalougPhoto->move(public_path('images'), $cataloug_photo);
        $post_catalogu = Catalogue::create([
            'provider_id' => $auth_user,
            'service_id' => $request->input('serviceId'),
            'catalog_name' => $request->input('catalougName'),
            'catalog_description' => $request->input('description'),
            'image' => $cataloug_photo,
            'service_duration' => $request->input('serviceDuration'),
            'salon_service_charge' => $request->input('serviceCharge'),
            'home_service_charge' => $request->input('homeServiceCharge'),
            'booking_money' => $request->input('bookingMoney'),
            'service_hour' => $request->input('serviceHoure'),
        ]);

        if ($post_catalogu) {
            return response()->json([
                'status' => 'success',
                'catalouge' => $post_catalogu,
            ], 200);
        } else {
            return ResponseErrorMessage('error', 'Catalouge add faile');
        }
    }

    public function getCataloug($id)
    {
        $all_provider_data = Catalogue::where('service_id', $id)->with('salonDetails')->get();
        $decodedData = [];
        foreach ($all_provider_data as $item) {
            $item['service_hour'] = json_decode($item['service_hour'], true);  // Decode only the 'module_class' field
            $item['image'] = json_decode($item['image'], true);
            $decodedData[] = $item;  // Add the updated item to the new array
        }

        if ($all_provider_data) {
            return response()->json([
                'status' => 'success',
                'provider' => $decodedData,
            ], 200);
        } else {
            return ResponseErrorMessage('error', 'Data not found');
        }
    }

    public function singleCataloug($id)
    {
        $service_catalouge = Catalogue::where('service_id', $id)->get();

        $decodedData = [];
        foreach ($service_catalouge as $item) {
            $item['available_service_our'] = json_decode($item['available_service_our'], true);  // Decode only the 'module_class' field
            $decodedData[] = $item;  // Add the updated item to the new array
        }

        if ($all_provider_data) {
            return response()->json([
                'status' => 'success',
                'provider' => $decodedData,
            ], 200);
        } else {
            return ResponseErrorMessage('error', 'Data not found');
        }

        if ($single_catalouge) {
            return response()->json([
                'status' => 'success',
                'Catalouge' => $single_catalouge
            ], 200);
        } else {
            return ResponseErrorMessage('error', 'Catalouge not found');
        }
    }

    public function updateCatalouge(CatalougeRequest $request)
    {
        $auth_user = auth()->user()->id;
        $update_catalouge = Catalogue::find($request->id);
        $update_catalouge->id = $request->id;
        $update_catalouge->provider_id = $auth_user;
        $update_catalouge->service_id = $request->serviceId;
        $update_catalouge->catalog_name = $request->catalougName;
        $update_catalouge->catalog_description = $request->description;
        $update_catalouge->service_duration = $request->serviceDuration;
        $update_catalouge->salon_service_charge = $request->serviceCharge;
        $update_catalouge->home_service_charge = $request->homeServiceCharge;
        $update_catalouge->booking_money = $request->bookingMoney;
        $update_catalouge->service_hour = $request->serviceHoure;
        $update_catalouge->save();
        if ($update_catalouge) {
            return ResponseMethod('success', 'update catalog success');
        } else {
            return ResponseErrorMessage('error', 'Catalouge update fail');
        }
    }

    public function updateCatalougeImg(Request $request)
    {
        $cataloug_photo = time() . '.' . $request->catalougPhoto->extension();
        $request->catalougPhoto->move(public_path('images'), $cataloug_photo);

        $update_catalouge_img = Catalogue::find($request->id);
        $update_catalouge_img->id = $request->id;
        $update_catalouge_img->image = $cataloug_photo;
        $update_catalouge_img->save();
        if ($update_catalouge_img) {
            return ResponseMethod('success', 'update catalog images success');
        } else {
            return ResponseErrorMessage('error', 'Catalouge update image fail');
        }
    }

    public function deleteCatalougImg(CatalougeRequest $request)
    {
        $deleteCatalougImg = Catalogue::find($request->id);
        $deleteCatalougImg->id = $request->id;
        if (file_exists('image' . $deleteCatalougImg->image) AND !empty($deleteCatalougImg->image)) {
            unlink('image' . $deleteCatalougImg->image);
        }
        $deleteCatalougImg->image = '';
        $deleteCatalougImg->save();
        if ($deleteCatalougImg == true) {
            return ResponseMethod('success', 'Catalouge images  delete success');
        } else {
            return ResponseErrorMessage('error', 'Catalouge image delete fail');
        }
    }

    public function deleteCatlouge($id)
    {
        $deleteCataloug = Catalogue::where('id', $id);
        if ($deleteCataloug == true) {
            return ResponseMethod('success', 'Catalouge   delete success');
        } else {
            return ResponseErrorMessage('error', 'Catalouge  delete fail');
        }
    }

    public function catalougeDetails($id)
    {
        $totlaReview = ServiceRating::where('catalogue_id', $id)->count();
        $sumRating = ServiceRating::where('catalogue_id', $id)->sum('rating');
        $avgRating = $sumRating / $totlaReview;
        $catalougeDetails = Catalogue::where('id', $id)->with('catalouges')->get();

        return response()->json([
            'message' => 'success',
            'review' => $totlaReview,
            'rating' => $avgRating,
            'cataloug_details' => $catalougeDetails
        ], 200);
    }
}
