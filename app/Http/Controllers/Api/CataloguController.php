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
            return ResponseMethod('success', 'Catalouge add successfully');
        } else {
            return ResponseErroMethod('error', 'Catalouge add faile');
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
            return ResponseErroMethod('error', 'Catalouge not found');
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
            return ResponseErroMethod('error', 'Catalouge not found');
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
            return ResponseMethod('success', 'update catalog success');
        } else {
            return ResponseErrorMethod('error', 'Catalouge update fail');
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
            return ResponseErrorMethod('error', 'Catalouge update image fail');
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
            return ResponseErrorMethod('error', 'Catalouge image delete fail');
        }
    }

    public function deleteCatlouge($id)
    {
        $deleteCataloug = Catalogue::where('id', $id);
        if ($deleteCataloug == true) {
            return ResponseMethod('success', 'Catalouge   delete success');
        } else {
            return ResponseErrorMethod('error', 'Catalouge  delete fail');
        }
    }
}
