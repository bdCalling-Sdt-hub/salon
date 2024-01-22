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
use App\Models\ServiceRating;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use DB;

class ProviderController extends Controller
{
    // ====================PROVIDER======================//

    public function postProvider(ProviderRequest $request)
    {
        $auth_user = auth()->user()->id;

        $image = array();
        if ($files = $request->file('photoGellary')) {
            foreach ($files as $file) {
                $gellery_photo = time() . '.' . $file->getClientOriginalName();
                $file->move(public_path('images'), $gellery_photo);
                $image[] = $gellery_photo;
            }
        }

        $cover_photo = time() . '.' . $request->coverPhoto->extension();
        $request->coverPhoto->move(public_path('images'), $cover_photo);
        $address = $request->address;

        $post_provider = new Provider();
        $post_provider->user_id = $auth_user;
        $post_provider->category_id = $request->catId;
        $post_provider->address = $request->address;
        $post_provider->business_name = $request->businessName;
        $post_provider->description = $request->description;
        $post_provider->available_service_our = $request->serviceOur;
        $post_provider->cover_photo = $cover_photo;
        $post_provider->gallary_photo = json_encode($image);
        $post_provider->latitude = $this->findLatitude($address);
        $post_provider->longitude = $this->findLongitude($address);
        $post_provider->save();
        if ($post_provider) {
            return ResponseMethod('success', $post_provider);
        } else {
            return ResponseErrorMessage('Add information faile');
        }
    }

    public function getProvider()
    {
        $user_id = auth()->user()->id;
        $getProvider = Provider::where('user_id', $user_id)->with('salonDetails')->get();
        // $all_provider_data = Provider::where('user_id', $user_id)->get();
        $decodedData = [];
        foreach ($getProvider as $item) {
            $item['available_service_our'] = json_decode($item['available_service_our'], true);  // Decode only the 'module_class' field
            $item['gallary_photo'] = json_decode($item['gallary_photo'], true);
            $decodedData[] = $item;  // Add the updated item to the new array
        }

        if ($getProvider) {
            return response()->json([
                'status' => 'success',
                'provider' => $decodedData,
            ], 200);
        } else {
            return ResponseErrorMessage('error', 'Data not found');
        }

        // {
        //     $user_id = auth()->user()->id;
        //     $getProvider = Provider::where('user_id', $user_id)->with('salonDetails')->get();

        //     // Use map to transform the collection
        //     $i = 0;
        //     foreach ($getProvider as &$item) {
        //         echo $i++ . $item;
        //         $item['available_service_our'] = json_decode($item['available_service_our'], true);
        //     }

        //     if ($getProvider) {
        //         return response()->json([
        //             'status' => 'success',
        //             'provider' => $getProvider,
        //         ], 200);
        //     } else {
        //         return ResponseErrorMessage('error', 'Data not found');
        //     }
        // }
    }

    public function editProvider($id)
    {
        $editProvider = Provider::where('id', $id)->first();
        if ($editProvider) {
            return response()->json([
                'status' => 'success',
                'provider' => $editProvider
            ], 200);
        } else {
            return ResponseErrorMethod('error', 'Data not found');
        }
    }

    public function updateProvider(Request $request)
    {
        $updateProvider = Provider::find($request->id);
        $updateProvider->category_id = $request->catId;
        $updateProvider->business_name = $request->businessName;
        $updateProvider->address = $request->address;
        $updateProvider->description = $request->description;
        $updateProvider->available_service_our = json_encode($request->serviceOur);
        $updateProvider->save();
        if ($updateProvider) {
            return ResponseMethod('success', 'provider update success');
        } else {
            return ResponseErrorMessage('error', 'provider update fail');
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
            return ResponseMethod('success', 'provider cover photo success');
        } else {
            return ResponseErrorMessage('error', 'update provider cover photo fail');
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
            return ResponseMethod('success', 'Provider cover images delete success');
        } else {
            return ResponseErrorMessage('error', 'Provider cover images  delete faile');
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
        $updateProviderCoverImg->gallary_photo = json_encode($image);
        $updateProviderCoverImg->save();

        if ($updateProviderCoverImg) {
            return ResponseMethod('success', 'update provider gallary photo success');
        } else {
            return ResponseErrorMessage('error', 'update provider gallary photo fail');
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
            return ResponseMethod('success', 'Provider gallary images delete success');
        } else {
            return ResponseErrorMessage('error', 'Provider gallary images  delete faile');
        }
    }

    public function deleteProvider($id)
    {
        $deleteProvider = Provider::where('id', $id)->delete();
        if ($deleteProvider == true) {
            return ResponseMethod('success', 'Provider delete success');
        } else {
            return ResponseErrorMessage('error', 'Provider delete faile');
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
        $auth_user = auth()->user()->id;
        $post_service = Service::create([
            'category_id' => $request->input('catId'),
            'provider_id' => $request->providerid,
            'service_name' => $request->input('serviceName'),
            'service_description' => $request->input('description'),
            'gallary_photo' => json_encode($image),
            'service_duration' => $request->input('serviceOur'),
            'salon_service_charge' => $request->input('serviceCharge'),
            'home_service_charge' => $request->input('homServiceCharge'),
            'set_booking_mony' => $request->input('bookingMony'),
            'available_service_our' => $request->input('serviceHour'),
        ]);

        if ($post_service) {
            return ResponseMethod('success', $post_service);
        } else {
            return ResponseErrorMessage('error', 'Service add faile');
        }
    }

    public function getService()
    {
        $auth_user = auth()->user()->id;
        $all_service = Service::where('provider_id', $auth_user)->get();
        if ($all_service) {
            return response()->json([
                'status' => 'success',
                'service' => $all_service
            ], 200);
        } else {
            return ResponseErrorMessage('error', 'Service data not found');
        }
    }

    public function serviceEdit($id)
    {
        $editService = Service::where('id', $id)->first();
        if ($editService) {
            return response()->json([
                'status' => 'success',
                'service' => $editService
            ], 200);
        } else {
            return ResponseErrorMessage('error', 'Service data not found');
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
        $updateService->available_service_our = json_encode($request->serviceHour);
        $updateService->save();
        if ($updateService) {
            return ResponseMethod('success', 'update service success');
        } else {
            return ResponseErrorMessage('error', 'update service faile');
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
        $updateServiceImg->gallary_photo = json_encode($image);
        $updateServiceImg->save();
        if ($updateServiceImg) {
            return ResponseMethod('success', 'update service image success');
        } else {
            return ResponseErrorMessage('error', 'update service image faile');
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
            return ResponseMethod('success', 'Service gallary images delete success');
        } else {
            return ResponseErrorMessage('error', 'Service gallary images delete success');
        }
    }

    public function serviceDelete($id)
    {
        $deleteService = Service::where('id', $id)->delete();
        if ($deleteService == true) {
            return ResponseMethod('success', 'Service delete success');
        } else {
            return ResponseErrorMessage('error', 'Service delete faile');
        }
    }

    public function providerAllService($id)
    {
        $allService = Service::where('provider_id', $id)->get();
        if ($allService == true) {
            return ResponseMethod('success', $allService);
        } else {
            return ResponseErrorMessage('error', 'Provider data not found');
        }
    }

    // ====================== Booking =================//

    public function booking()
    {
        $authUser = auth()->user()->id;
        $getBooking = Booking::where('provider_id', $authUser)->get();

        if ($getBooking) {
            return ResponseMethod('success', $getBooking);
        } else {
            return ResponseErrorMessage('error', 'Booking data not found');
        }
    }

    public function bookingRequest()
    {
        $authUser = auth()->user()->id;
        $getBooking = Booking::where('provider_id', $authUser)->where('status', '0')->get();

        if ($getBooking) {
            return ResponseMethod('success', $getBooking);
        } else {
            return ResponseErrorMessage('error', 'Booking data not found');
        }
    }

    public function bookingDetails($id)
    {
        $authUser = auth()->user();
        if ($authUser) {
            $editBooking = Booking::where('id', $id)->first();
            if ($editBooking) {
                return ResponseMethod('success', $editBooking);
            } else {
                return ResponseErrorMessage('error', 'Booking data not found');
            }
        }
    }

    public function re_shedule_appoinment(Request $request)
    {
        $authUser = auth()->user();
        if ($authUser) {
            $date = $request->date;
            $time = $request->time;
            $scedulCheck = Booking::where('date', $date)->where('time', $time)->count();
            if ($scedulCheck) {
                return ResponseErrorMethod('false', 'Sloat not avlable');
            } else {
                $updateBooking = Booking::find($request->id);
                $updateBooking->date = $request->date;
                $updateBooking->time = $request->time;
                $updateBooking->save();

                if ($updateBooking) {
                    return response()->json([
                        'status' => 'success',
                        'Notification' => sendNotification('Booking re-shedule', $updateBooking),
                    ], 200);
                } else {
                    return ResponseErrorMessage('false', 'Booking update faile');
                }
            }
        }
    }

    public function bookingAccept(Request $request)
    {
        $bookingInfo = Booking::where('id', $request->id)->get();
        $updateStatus = Booking::find($request->id);
        $updateStatus->status = $request->status;
        $updateStatus->save();
        if ($updateStatus) {
            return response()->json([
                'status' => 'success',
                'Notification' => sendNotification('Booking accepted', $updateStatus),
                'message' => 'Accept your request'
            ], 200);
        } else {
            return ResponseErrorMessage('error', 'Booking status update faile');
        }
    }

    public function decline($id)
    {
        $cancelBooking = Booking::where('id', $id)->delete();

        if ($cancelBooking) {
            return ResponseMethod('success', 'Booking delete success');
        } else {
            return ResponseErrorMethod('error', 'Booking delete faile');
        }
    }

    public function approvedBooking()
    {
        $authUser = auth()->user()->id;
        $getBooking = Booking::where('provider_id', $authUser)->get();

        if ($getBooking) {
            return ResponseMethod('success', $getBooking);
        } else {
            return ResponseErrorMessage('error', 'Booking data not found');
        }
    }

    public function bookingHistory()
    {
        $authUser = auth()->user()->id;
        $getBooking = Booking::where('provider_id', $authUser)->get();

        if ($getBooking) {
            return ResponseMethod('success', $getBooking);
        } else {
            return ResponseErrorMessage('error', 'Booking data not found');
        }
    }

    public function reviewProvider()
    {
        $authUser = auth()->user()->id;
        if ($authUser) {
            $allReview = ServiceRating::where('provider_id', $authUser)->get();
            if ($allReview) {
                return ResponseMethod('success', $allReview);
            } else {
                return ResponseErrorMessage('error', 'Booking data not found');
            }
        }
    }

    public function findLatitude($address)
    {
        $result = app('geocoder')->geocode($address)->get();
        $coordinates = $result[0]->getCoordinates();
        $lat = $coordinates->getLatitude();
        return $lat;
    }

    public function findLongitude($address)
    {
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
