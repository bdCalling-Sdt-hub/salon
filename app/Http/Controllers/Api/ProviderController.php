<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProviderRequest;
use App\Http\Requests\ServiceRequest;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function postProvider(ProviderRequest $request)
    {
        $cover_photo = time() . '.' . $request->coverPhoto->extension();
        $request->coverPhoto->move(public_path('images'), $cover_photo);

        $image = array();
        if ($files = $request->file('photoGellary')) {
            foreach ($files as $file) {
                $gellery_photo = time() . '.' . $file->getClientOriginalName();
                $file->move(public_path('images'), $gellery_photo);
                $image[] = $gellery_photo;
            }
        }
        $post_provider = Provider::create([
            'category_id' => $request->input('catId'),
            'business_name' => $request->input('businessName'),
            'address' => $request->input('address'),
            'description' => $request->input('description'),
            'available_service_our' => $request->input('serviceOur'),
            'cover_photo' => $cover_photo,
            'gallary_photo' => implode('|', $image),
        ]);

        if ($post_provider) {
            return response()->json([
                'status' => 'success',
                'message' => 'Provider add successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Provider add faile'
            ]);
        }
    }

    public function getProvider()
    {
        $all_provider_data = Provider::orderBy('id', 'desc')->get();
        if ($all_provider_data) {
            return response()->json([
                'status' => 'true',
                'service' => $all_provider_data
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Data not found'
            ]);
        }
    }

    public function postService(ServiceRequest $request)
    {
        $image = array();
        if ($files = $request->file('servicePhotoGellary')) {
            foreach ($files as $file) {
                $service_gellery_photo = time() . '.' . $file->getClientOriginalName();
                $file->move(public_path('images'), $service_gellery_photo);
                $image[] = $service_gellery_photo;
            }
        }
        $post_service = Service::create([
            'category_id' => $request->input('catId'),
            'provider_id' => $request->input('providerId'),
            'service_name' => $request->input('serviceName'),
            'service_description' => $request->input('description'),
            'gallary_photo' => implode('|', $image),
            'service_duration' => $request->input('serviceOur'),
            'salon_service_charge' => $request->input('serviceCharge'),
            'home_service_charge' => $request->input('homServiceCharge'),
            'set_booking_mony' => $request->input('bookingMony'),
            'available_service_our' => $request->input('serviceHour'),
        ]);
        if ($post_service) {
            return response()->json([
                'status' => 'success',
                'message' => 'Service add successfully'
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Service add faile'
            ]);
        }
    }

    public function getService()
    {
        $all_service = Service::orderBy('id', 'desc')->get();
        if ($all_service) {
            return response()->json([
                'status' => 'true',
                'service' => $all_service
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Service add faile'
            ]);
        }
    }
}
