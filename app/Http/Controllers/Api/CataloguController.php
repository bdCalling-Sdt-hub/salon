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
        $request->catalougPhoto->move(public_path('images'), $cataloug_photo);
        $post_catalogu = Catalogue::create([
            'provider_id' => $request->input('providerId'),
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
                'message' => 'Catalouge add successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Catalouge add faile'
            ]);
        }
    }

    public function getCataloug()
    {
        $get_catalouge = Catalogue::all();

        if ($get_catalouge) {
            return response()->json([
                'status' => 'success',
                'Catalouge' => $get_catalouge
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Catalouge not found'
            ]);
        }
    }

    public function singleCataloug($id)
    {
        $single_catalouge = Catalogue::where('id', $id)->get();

        if ($single_catalouge) {
            return response()->json([
                'status' => 'success',
                'Catalouge' => $single_catalouge
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Catalouge not found'
            ]);
        }
    }

    public function updateCatalouge(CatalougeRequest $request)
    {
        $update_catalouge = Catalogue::find($request->id);
        $update_catalouge->id = $request->id;
        $update_catalouge->provider_id = $request->providerId;
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
            return response()->json([
                'status' => 'success',
                'message' => 'update catalog success'
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Catalouge update fail'
            ]);
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
            return response()->json([
                'status' => 'success',
                'message' => 'update catalog images success'
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Catalouge update image fail'
            ]);
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
            return response()->json([
                'status' => 'success',
                'message' => 'Catalouge images delete success'
            ]);
        } else {
            return response()->json([
                'status' => 'faile',
                'message' => 'Catalouge images  delete faile'
            ]);
        }
    }

    public function deleteCatlouge($id)
    {
        $deleteCataloug = Catalogue::where('id', $id);
        if ($deleteCataloug == true) {
            return response()->json([
                'status' => 'success',
                'message' => 'Catalouge  delete success'
            ]);
        } else {
            return response()->json([
                'status' => 'faile',
                'message' => 'Catalouge   delete faile'
            ]);
        }
    }
}
