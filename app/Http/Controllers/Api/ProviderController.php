<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\ProviderRequest;
use App\Http\Requests\ServiceRequest;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    // ====================PROVIDER======================//

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
        $address = $request->address;
        $post_provider = Provider::create([
            'category_id' => $request->input('catId'),
            'business_name' => $request->input('businessName'),
            'address' => $request->input('address'),
            'description' => $request->input('description'),
            'available_service_our' => $request->input('serviceOur'),
            'cover_photo' => $cover_photo,
            'gallary_photo' => implode('|', $image),
            'latitude' => $this->findLatitude($address),
            'longitude' => $this->findLongitude($address),
        ]);
        if ($post_provider) {

            return response()->json([
                'status' => 'success',
                'message' => 'post added successfully',
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Provider add failed'
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

    public function editProvider($id)
    {
        $editProvider = Provider::where('id', $id)->first();
        if ($editProvider) {
            return response()->json([
                'status' => 'success',
                'provider' => $editProvider
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'provider' => $editProvider
            ]);
        }
    }

    public function updateProvider(Request $request)
    {
        $updateProvider = Provider::find($request->id);
        $updateProvider->category_id = $request->catId;
        $updateProvider->business_name = $request->businessName;
        $updateProvider->address = $request->address;
        $updateProvider->description = $request->description;
        $updateProvider->available_service_our = $request->serviceOur;
        $updateProvider->save();
        if ($updateProvider) {
            return response()->json([
                'status' => 'success',
                'message' => 'provider update success',
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'provider update fail',
            ]);
        }
    }

    public function providerCoverPhotoUpdate(Request $request)
    {
        $cover_photo_update = time() . '.' . $request->coverPhoto->extension();
        $request->coverPhoto->move(public_path('images'), $cover_photo_update);

        $updateProviderCoverImg = Provider::find($request->id);
        $updateProviderCoverImg->cover_photo = $cover_photo_update;
        $updateProviderCoverImg->save();

        if ($updateProviderCoverImg) {
            return response()->json([
                'status' => 'success',
                'message' => 'update provider cover photo success',
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'update provider cover photo fail',
            ]);
        }
    }

    public function deleteProviderCoverImg(Request $request)
    {
        $deleteProviderCoverImg = Provider::find($request->id);
        $deleteProviderCoverImg->id = $request->id;
        if (file_exists('cover_photo' . $deleteProviderCoverImg->cover_photo) AND !empty($deleteProviderCoverImg->cover_photo)) {
            unlink('cover_photo' . $deleteProviderCoverImg->cover_photo);
        }
        $deleteProviderCoverImg->cover_photo = '';
        $deleteProviderCoverImg->save();
        if ($deleteProviderCoverImg == true) {
            return response()->json([
                'status' => 'success',
                'message' => 'Provider cover images delete success'
            ]);
        } else {
            return response()->json([
                'status' => 'faile',
                'message' => 'Provider cover images  delete faile'
            ]);
        }
    }

    public function providerGallaryPhotoUpdate(Request $request)
    {
        $image = array();
        if ($files = $request->file('photoGellary')) {
            foreach ($files as $file) {
                $gellery_photo = time() . '.' . $file->getClientOriginalName();
                $file->move(public_path('images'), $gellery_photo);
                $image[] = $gellery_photo;
            }
        }

        $updateProviderCoverImg = Provider::find($request->id);
        $updateProviderCoverImg->gallary_photo = $image;
        $updateProviderCoverImg->save();

        if ($updateProviderCoverImg) {
            return response()->json([
                'status' => 'success',
                'message' => 'update provider gallary photo success',
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'update provider gallary photo fail',
            ]);
        }
    }

    public function deleteProviderGallary(Request $request)
    {
        $deleteProviderGallaryImg = Provider::find($request->id);
        if (file_exists('gallary_photo' . $deleteProviderGallaryImg->gallary_photo) AND !empty($deleteProviderGallaryImg->gallary_photo)) {
            unlink('gallary_photo' . $deleteProviderGallaryImg->gallary_photo);
        }
        $deleteProviderGallaryImg->gallary_photo = '';
        $deleteProviderGallaryImg->save();
        if ($deleteProviderGallaryImg == true) {
            return response()->json([
                'status' => 'success',
                'message' => 'Provider gallary images delete success'
            ]);
        } else {
            return response()->json([
                'status' => 'faile',
                'message' => 'Provider gallary images  delete faile'
            ]);
        }
    }

    public function deleteProvider($id)
    {
        $deleteProvider = Provider::where('id', $id)->delete();
        if ($deleteProvider == true) {
            return response()->json([
                'status' => 'success',
                'message' => 'Provider delete success'
            ]);
        } else {
            return response()->json([
                'status' => 'faile',
                'message' => 'Provider delete faile'
            ]);
        }
    }

    // ========================= SERVICE =======================//

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

    public function serviceEdit($id)
    {
        $editService = Service::where('id', $id)->first();
        if ($editService) {
            return response()->json([
                'status' => 'true',
                'service' => $editService
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Service data not found'
            ]);
        }
    }

    public function updateService(Request $request)
    {
        $updateService = Service::find($request->id);
        $updateService->id = $request->id;
        $updateService->category_id = $request->catId;
        $updateService->provider_id = $request->providerId;
        $updateService->service_name = $request->serviceName;
        $updateService->service_description = $request->description;
        $updateService->service_duration = $request->serviceOur;
        $updateService->salon_service_charge = $request->serviceCharge;
        $updateService->home_service_charge = $request->homServiceCharge;
        $updateService->set_booking_mony = $request->bookingMony;
        $updateService->available_service_our = $request->serviceHour;
        $updateService->save();
        if ($updateService) {
            return response()->json([
                'status' => 'true',
                'message' => 'update service success'
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'update service faile'
            ]);
        }
    }

    public function updateServiceImage(Request $request)
    {
        $image = array();
        if ($files = $request->file('servicePhotoGellary')) {
            foreach ($files as $file) {
                $service_gellery_photo = time() . '.' . $file->getClientOriginalName();
                $file->move(public_path('images'), $service_gellery_photo);
                $image[] = $service_gellery_photo;
            }
        }
        $updateServiceImg = Service::find($request->id);
        $updateServiceImg->id = $request->id;
        $updateServiceImg->gallary_photo = implode('|', $image);
        $updateServiceImg->save();
        if ($updateServiceImg) {
            return response()->json([
                'status' => 'true',
                'message' => 'update service image success'
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'update service image faile'
            ]);
        }
    }

    public function deleteServiceGallary(Request $request)
    {
        $deleteServiceGallaryImg = Service::find($request->id);
        $deleteServiceGallaryImg->id = $request->id;
        if (file_exists('gallary_photo' . $deleteServiceGallaryImg->gallary_photo) AND !empty($deleteServiceGallaryImg->gallary_photo)) {
            unlink('gallary_photo' . $deleteServiceGallaryImg->gallary_photo);
        }
        $deleteServiceGallaryImg->gallary_photo = '';
        $deleteServiceGallaryImg->save();
        if ($deleteServiceGallaryImg == true) {
            return response()->json([
                'status' => 'success',
                'message' => 'Service gallary images delete success'
            ]);
        } else {
            return response()->json([
                'status' => 'faile',
                'message' => 'Service gallary images  delete faile'
            ]);
        }
    }

    public function serviceDelete($id)
    {
        $deleteService = Service::where('id', $id)->delete();
        if ($deleteService == true) {
            return response()->json([
                'status' => 'success',
                'message' => 'Service delete success'
            ]);
        } else {
            return response()->json([
                'status' => 'faile',
                'message' => 'Service delete faile'
            ]);
        }
    }

    public function providerAllService($id)
    {
        $allService = Service::where('provider_id', $id)->get();
        return $allService;
    }

    // ====================== Booking =================//

    public function postBooking(BookingRequest $request)
    {
        $post_booking = Booking::create([
            'user_id' => $request->input('userId'),
            'provider_id' => $request->input('providerId'),
            'service_id' => $request->input('serviceId'),
            'service' => $request->input('service'),
            'price' => $request->input('price'),
            'date' => $request->input('date'),
            'time' => $request->input('time'),

        ]);

        if ($post_booking) {
            return response()->json([
                'status' => 'true',
                'message' => 'Booking success'
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Booking faile'
            ]);
        }
    }

    public function getBooking()
    {
        $getBooking = Booking::all();
        if ($getBooking) {
            return response()->json([
                'status' => 'true',
                'message' => $getBooking
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Booking data not found',
            ]);
        }
    }

    public function editBooking($id)
    {
        $editBooking = Booking::where('id', $id)->first();
        if ($editBooking) {
            return response()->json([
                'status' => 'success',
                'booking' => $editBooking
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Booking data not found',
            ]);
        }
    }

    public function updateBooking(Request $request)
    {
        $date = $request->date;
        $time = $request->time;
        $scedulCheck = Booking::where('date', $date)->where('time', $time)->count();
        if ($scedulCheck) {
            return response()->json([
                'status' => false,
                'message' => 'Sloat not avlable'
            ]);
        } else {
            $updateBooking = Booking::find($request->id);
            $updateBooking->date = $request->date;
            $updateBooking->time = $request->time;
            $updateBooking->save();
            if ($updateBooking) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Booking update success',
                ]);
            } else {
                return response()->json([
                    'status' => 'false',
                    'message' => 'Booking update faile',
                ]);
            }
        }
    }

    public function updateStatus(Request $request)
    {
        $updateStatus = Booking::find($request->id);
        $updateStatus->status = $request->status;
        $updateStatus->save();
        if ($updateStatus) {
            return response()->json([
                'status' => 'success',
                'message' => 'Booking status update success',
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Booking status update faile',
            ]);
        }
    }

    public function cancelBooking($id)
    {
        $cancelBooking = Booking::where('id', $id)->delete();

        if ($cancelBooking) {
            return response()->json([
                'status' => 'success',
                'message' => 'Booking delete success',
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Booking delete faile',
            ]);
        }
    }

    public function category()
    {
        // return Category::with('provider')->get();

        $data = Category::join('providers', 'providers.category_id', '=', 'categories.id')
            ->join('services', 'services.provider_id', '=', 'providers.id')
            ->get();
        return response()->json([
            'status' => 'success',
            'Category' => $data
        ]);
    }


    public function findLatitude($address){
        $result = app('geocoder')->geocode($address)->get();
        $coordinates = $result[0]->getCoordinates();
        $lat = $coordinates->getLatitude();
        return $lat;
    }
    public function findLongitude($address){
        $result = app('geocoder')->geocode($address)->get();
        $coordinates = $result[0]->getCoordinates();
        $long = $coordinates->getLongitude();
        return $long;
    }

//    public function findLatitude($address)
//    {
//        $result = app('geocoder')->geocode($address)->get();
//
//        if (!empty($result) && $result->count() > 0) {
//            $coordinates = $result[0]->getCoordinates();
//            $lat = $coordinates->getLatitude();
//            return $lat;
//        } else {
//            // Handle the case where geocoding was unsuccessful
//            return null;
//        }
//    }
//
//    public function findLongitude($address)
//    {
//        $result = app('geocoder')->geocode($address)->get();
//
//        if (!empty($result) && $result->count() > 0) {
//            $coordinates = $result[0]->getCoordinates();
//            $long = $coordinates->getLongitude();
//            return $long;
//        } else {
//            // Handle the case where geocoding was unsuccessful
//            return null;
//        }
//    }
}
