<?php

namespace App\Http\Controllers\Api;

use App\Events\SendNotification;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Http\Requests\ProviderRequest;
use App\Http\Requests\ServiceRequest;
use App\Models\Booking;
use App\Models\Catalogue;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
use App\Models\ServiceRating;
use App\Notifications\providerNotification;
use Carbon\Carbon;
use Geocoder\Laravel\Facades\Geocoder;
use http\Env\Response;
use Illuminate\Http\Request;
use DB;

class ProviderController extends Controller
{
    // Notification j//

    public function new_booking_request()
    {
        // $auth_user = auth()->user()->id;
        // $provider = Provider::where('user_id', $auth_user)->first();

        // $notifications = DB::table('notifications')
        //     ->where('type', 'App\Notifications\SalonNotification')
        //     ->orderBy('created_at', 'desc')
        //     ->paginate(10);

        // $notificationsForProvider4 = [];

        // foreach ($notifications as $notification) {
        //     $data = json_decode($notification->data);

        //     if (isset($data->user->provider_id) && $data->user->provider_id === $provider->id) {
        //         $notificationData = [
        //             'id' => $notification->id,
        //             'read_at' => $notification->read_at,
        //             'type' => $notification->type,
        //             'data' => $data,
        //         ];
        //         $notificationsForProvider4[] = $notificationData;
        //     }
        // }

        // return response()->json([
        //     'status' => 'success',
        //     'notification' => $notificationsForProvider4,
        //     'user_notification' => $this->account_notification(),
        //     'next_page_url' => $notifications->nextPageUrl()
        // ]);

        $user = auth()->user();

        if ($user) {
            $userId = $user->id;

            $query = DB::table('notifications')
                ->where('notifications.type', 'App\Notifications\SalonNotification')
                ->where(function ($query) use ($userId) {
                    $query
                        ->where(function ($query) use ($userId) {
                            $query
                                ->where('notifiable_type', 'App\Models\User')
                                ->where('notifiable_id', $userId);
                        })
                        ->orWhere(function ($query) use ($userId) {
                            $query->whereJsonContains('data->user->user_id', $userId);
                        });
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $user_notifications = $query->map(function ($notification) {
                $notification->data = json_decode($notification->data);
                return $notification;
            });

            return response()->json([
                'message' => 'Notification list',
                'notifications' => $user_notifications,
            ], 200);
        }
        return response()->json([
            'message' => 'Notification list',
            'notifications' => [],
        ], 200);
    }

    // public function account_notification()
    // {
    //     $user = auth()->user();
    //     return $notifications = $user->notifications;
    // }

    public function readAtNotification(Request $request)
    {
        $notification = DB::table('notifications')->find($request->id);
        if ($notification) {
            $notification->read_at = Carbon::now();
            DB::table('notifications')->where('id', $notification->id)->update(['read_at' => $notification->read_at]);
            return response()->json([
                'status' => 'success',
                'message' => 'Notification read successfully.',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Notification not found',
            ], 404);
        }
    }

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
            return ResponseErrorMessage('Add information failed');
        }
    }


    public function getProvider()
    {
        $user_id = auth()->user()->id;

        $getProvider = Provider::where('user_id', $user_id)->with('salonDetails')->get();
        $decodedData = [];
        foreach ($getProvider->toArray() as $item) {
            $item['available_service_our'] = json_decode($item['available_service_our'], true);
            $item['gallary_photo'] = json_decode($item['gallary_photo'], true);

            // Loop through salon_details and decode available_service_our for each item
            foreach ($item['salon_details'] as &$salonDetail) {
                $salonDetail['available_service_our'] = json_decode($salonDetail['available_service_our'], true);
                $salonDetail['gallary_photo'] = json_decode($salonDetail['gallary_photo'], true);
            }

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
    }

    public function updateProvider(Request $request)
    {
        $image = array();

        // Handle gallery photos
        if ($files = $request->file('photoGellary')) {
            foreach ($files as $file) {
                $gallary_photo = time() . '.' . $file->getClientOriginalName();
                $file->move(public_path('images'), $gallary_photo);
                $image[] = $gallary_photo;
            }
        }

        $updateProvider = Provider::find($request->id);

        // Handle cover photo
        if ($request->file('coverPhoto')) {
            // Check if the old cover photo file exists before attempting to unlink it
            if (file_exists(public_path('images') . '/' . $updateProvider->cover_photo)) {
                unlink(public_path('images') . '/' . $updateProvider->cover_photo);
            }

            $cover_photo = time() . '.' . $request->file('coverPhoto')->getClientOriginalName();
            $request->file('coverPhoto')->move(public_path('images'), $cover_photo);
            $updateProvider->cover_photo = $cover_photo;
        }

        $updateProvider->id = $request->id;
        $updateProvider->category_id = $request->catId;
        $updateProvider->business_name = $request->businessName;
        $updateProvider->address = $request->address;
        $updateProvider->description = $request->description;
        $updateProvider->available_service_our = $request->serviceOur;

        // Check if $image is empty before updating 'gallery_photo'
        if (!empty($image)) {
            $updateProvider->gallary_photo = json_encode($image);
        }

        $updateProvider->update();

        if ($updateProvider) {
            return ResponseMethod('success', 'provider update success');
        } else {
            return response()->json([
                'error' => 'update provider not found'
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
        if ($request)
            $image = array();
        if ($files = $request->file('servicePhotoGellary')) {
            foreach ($files as $file) {
                $service_gellery_photo = time() . '.' . $file->getClientOriginalName();
                $file->move(public_path('images'), $service_gellery_photo);
                $image[] = $service_gellery_photo;
            }
        }

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

        // Check if $image is empty before updating 'gallery_photo'
        if (!empty($image)) {
            $updateService->gallary_photo = json_encode($image);
        }
        // $updateService->gallary_photo = json_encode($image);

        $updateService->available_service_our = $request->serviceHour;
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
        // $allService = Service::where('provider_id', $id)->get();
        // if ($allService == true) {
        //     return ResponseMethod('success', $allService);
        // } else {
        //     return ResponseErrorMessage('error', 'Provider data not found');
        // }

        $getServices = Service::where('provider_id', $id)->get();
        $decodedData = [];
        foreach ($getServices->toArray() as $item) {
            $item['available_service_our'] = json_decode($item['available_service_our'], true);
            $item['gallary_photo'] = json_decode($item['gallary_photo'], true);
            $decodedData[] = $item;  // Add the updated item to the new array
        }

        if ($getServices) {
            return response()->json([
                'message' => 'success',
                'services' => $decodedData,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Service Not Found',
                'data' => []
            ], 200);
        }
    }

    // ====================== Booking =================//

    public function booking()
    {
        try {
            $authUser = auth()->user()->id;
            $provider = Provider::where('user_id', $authUser)->first();

            if (!$provider) {
                // throw new \Exception('Provider not found.');
                return response()->json([
                    'message' => 'No provider history found.',
                    'data' => []
                ]);
            }

            $providerId = $provider->id;
            $getBooking = Booking::where('provider_id', $providerId)->with('user')->get();

            if ($getBooking->isEmpty()) {
                //  throw new \Exception('No booking history found.');
                return response()->json([
                    'message' => 'No booking history found.',
                    'data' => []
                ]);
            }

            $decodedBookings = [];

            foreach ($getBooking as $booking) {
                $decodedServices = json_decode($booking->service, true);

                if (!is_array($decodedServices)) {
                    throw new \Exception('Error decoding the service JSON.');
                }

                foreach ($decodedServices as $service) {
                    $catalogIds = explode(',', $service['catalouge_id']);
                    $catalogDetails = [];

                    foreach ($catalogIds as $catalogId) {
                        $catalog = Catalogue::find($catalogId);

                        if ($catalog) {
                            $catalogDetails[] = $catalog;
                        }
                    }

                    $decodedBookings[] = [
                        'booking' => $booking,
                        'service' => $service,
                        'catalog_details' => $catalogDetails,
                    ];
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $decodedBookings,
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bookingRequest()
    {
        try {
            $authUser = auth()->user()->id;
            $provider = Provider::where('user_id', $authUser)->first();

            if (!$provider) {
                throw new \Exception('Provider not found.');
            }

            $providerId = $provider->id;
            $getBooking = Booking::where('provider_id', $providerId)
                ->where('status', '0')
                ->with('user')
                ->paginate(10);

            if ($getBooking->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No booking history found.',
                    'data' => []
                ], 200);
            }

            $decodedBookings = [];

            foreach ($getBooking as $booking) {
                $bookingList = [];  // Reset booking list for each booking

                $decodedServices = json_decode($booking->service, true);

                if (!is_array($decodedServices)) {
                    throw new \Exception('Error decoding the service JSON.');
                }

                foreach ($decodedServices as $service) {
                    // Assuming each service has only one catalog_id
                    $catalogIds = $service['catalouge_id'];

                    $catalog = Catalogue::find($catalogIds);

                    if ($catalog) {
                        $bookingList[] = $catalog;
                    }
                }

                $decodedBookings[] = [
                    'booking' => $booking,
                    'catalog_details' => $bookingList,
                ];
            }
            return response()->json([
                'data' => $decodedBookings,
                'pagination' => [
                    'current_page' => $getBooking->currentPage(),
                    'total_pages' => $getBooking->lastPage(),
                    'per_page' => $getBooking->perPage(),
                    'total' => $getBooking->total(),
                    'next_page_url' => $getBooking->nextPageUrl(),
                    'prev_page_url' => $getBooking->previousPageUrl(),
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bookingDetails($id)
    {
        try {
            $authUser = auth()->user()->id;
            $provider = Provider::where('user_id', $authUser)->first();

            if (!$provider) {
                throw new \Exception('Provider not found.');
            }

            $providerId = $provider->id;
            $booking = Booking::where('id', $id)->with('user')->first();

            if (!$booking) {
                // throw new \Exception('Booking not found.');
                return response()->json([
                    'status' => 'error',
                    'message' => 'No booking history found.',
                    'data' => []
                ], 500);
            }

            $decodedServices = json_decode($booking->service, true);

            if (!is_array($decodedServices)) {
                throw new \Exception('Error decoding the service JSON.');
            }

            $decodedBookings = [];

            foreach ($decodedServices as $service) {
                $catalogIds = explode(',', $service['catalouge_id']);
                $catalogDetails = [];

                foreach ($catalogIds as $catalogId) {
                    $catalog = Catalogue::find($catalogId);

                    if ($catalog) {
                        $catalogDetails[] = $catalog;
                    }
                }

                $decodedBookings[] = [
                    'booking' => $booking,
                    'service' => $service,
                    'catalog_details' => $catalogDetails,
                ];
            }

            return response()->json([
                'decoded_bookings' => $decodedBookings,
            ]);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
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
                return response()->json([
                    'message' => 'Slot Not available',
                ], 402);
            } else {
                $updateBooking = Booking::find($request->id);
                $updateBooking->date = $request->date;
                $updateBooking->time = $request->time;
                $updateBooking->save();

                if ($updateBooking) {
                    return response()->json([
                        'status' => 'success',
                        'Notification' => sendNotification('Booking re-shedule', 'Booking re-shedule', $updateBooking),
                    ], 200);
                } else {
                    return response()->json([
                        'message' => 'Booking Update failed',
                    ], 402);
                }
            }
        }
    }

    public function bookingAccept(Request $request)
    {
        $updateStatus = Booking::find($request->id);

        if (!$updateStatus) {
            return response()->json([
                'message' => 'Booking not found'
            ], 404);
        }

        $updateStatus->status = $request->status;
        $updateStatus->save();

        $statusMessages = [
            1 => 'Booking approved',
            2 => 'Booking completed',
            3 => 'Marked as late',
            4 => 'Marked as Cancel',
            5 => 'Service started',
            6 => 'Service ended'
        ];

        if (isset($statusMessages[$request->status])) {
            if ($request->status == 1) {
                return response()->json([
                    'status' => 'success',
                    'message' => $statusMessages[$request->status],
                    'Notification' => sendNotification('Accept your booking', 'Accept your booking', $updateStatus),
                ], 200);
            }
            return response()->json([
                'status' => 'success',
                'message' => $statusMessages[$request->status],
            ], 200);
        } else {
            return response()->json([
                'message' => 'Invalid status provided'
            ], 400);
        }
    }

    public function decline($id)
    {
        $cancelBooking = Booking::where('id', $id)->delete();

        if ($cancelBooking) {
            return ResponseMethod('success', 'Booking delete success');
        } else {
            return ResponseErrorMethod('error', 'Booking delete failed');
        }
    }

    // previous approvedBooking

    // public function approvedBooking()
    // {
    //     try {
    //         $authUser = auth()->user()->id;
    //         $provider = Provider::where('user_id', $authUser)->first();

    //         if (!$provider) {
    //             throw new \Exception('Provider not found.');
    //         }

    //         $providerId = $provider->id;
    //         $getBooking = Booking::where('provider_id', $providerId)->where('status', '1')->with('user')->paginate(2);

    //         if ($getBooking->isEmpty()) {
    //             // throw new \Exception('No booking history found.');
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'No booking history found.',
    //                 'data' => []
    //             ], 500);
    //         }

    //         $decodedBookings = [];

    //         foreach ($getBooking as $booking) {
    //             $decodedServices = json_decode($booking->service, true);

    //             if (!is_array($decodedServices)) {
    //                 throw new \Exception('Error decoding the service JSON.');
    //             }

    //             foreach ($decodedServices as $service) {
    //                 $catalogIds = explode(',', $service['catalouge_id']);
    //                 $catalogDetails = [];

    //                 foreach ($catalogIds as $catalogId) {
    //                     $catalog = Catalogue::find($catalogId);

    //                     if ($catalog) {
    //                         $catalogDetails[] = $catalog;
    //                     }
    //                 }

    //                 $decodedBookings[] = [
    //                     'booking' => $booking,
    //                     'service' => $service,
    //                     'catalog_details' => $catalogDetails,
    //                 ];
    //             }
    //         }

    //         return response()->json([
    //             'decoded_bookings' => $decodedBookings,
    //         ]);
    //     } catch (\Exception $e) {
    //         // Handle the exception
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    public function approvedBooking()
    {
        try {
            $authUser = auth()->user()->id;
            $provider = Provider::where('user_id', $authUser)->first();

            if (!$provider) {
                throw new \Exception('Provider not found.');
            }

            $providerId = $provider->id;
            $bookings = Booking::where('provider_id', $providerId)
//                ->where('status', '1')
                ->whereIn('status', [1, 6])
                ->with(['user' => function ($bookings) {
                    $bookings->where('user_status', 1);
                }])
                ->orderBy('id', 'DESC')  // Ordering by ID in descending order for pagination
                ->paginate(10);  // Change 10 to the desired number of bookings per page

            if ($bookings->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No Approved history found.',
                    'data' => []
                ], 200);
            }

            $decodedBookings = [];

            foreach ($bookings as $booking) {
                $bookingList = [];  // Reset booking list for each booking

                $decodedServices = json_decode($booking->service, true);

                if (!is_array($decodedServices)) {
                    throw new \Exception('Error decoding the service JSON.');
                }

                foreach ($decodedServices as $service) {
                    // Assuming each service has only one catalog_id
                    $catalogIds = $service['catalouge_id'];

                    $catalog = Catalogue::find($catalogIds);

                    if ($catalog) {
                        $bookingList[] = $catalog;
                    }
                }

                $decodedBookings[] = [
                    'booking' => $booking,
                    'catalog_details' => $bookingList,
                ];
            }

            return response()->json([
                'data' => $decodedBookings,
                'pagination' => [
                    'total' => $bookings->total(),
                    'per_page' => $bookings->perPage(),
                    'current_page' => $bookings->currentPage(),
                    'last_page' => $bookings->lastPage(),
                    'from' => $bookings->firstItem(),
                    'to' => $bookings->lastItem()
                ]
            ]);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // public function approvedBooking()
    // {
    //     try {
    //         $authUser = auth()->user()->id;
    //         $provider = Provider::where('user_id', $authUser)->first();

    //         if (!$provider) {
    //             throw new \Exception('Provider not found.');
    //         }

    //         $providerId = $provider->id;
    //         $getBooking = Booking::where('provider_id', $providerId)
    //             ->where('status', '1')
    //             ->with('user')
    //             ->paginate(10);

    //         if ($getBooking->isEmpty()) {
    //             return response()->json([
    //                 'status' => 'error',
    //                 'message' => 'No Approved history found.',
    //                 'data' => []
    //             ], 500);
    //         }

    //         $decodedBookings = [];

    //         foreach ($getBooking as $booking) {
    //             $bookingList = [];  // Reset booking list for each booking

    //             $decodedServices = json_decode($booking->service, true);

    //             if (!is_array($decodedServices)) {
    //                 throw new \Exception('Error decoding the service JSON.');
    //             }

    //             foreach ($decodedServices as $service) {
    //                 // Assuming each service has only one catalog_id
    //                 $catalogIds = $service['catalouge_id'];

    //                 $catalog = Catalogue::find($catalogIds);

    //                 if ($catalog) {
    //                     $bookingList[] = $catalog;
    //                 }
    //             }

    //             $decodedBookings[] = [
    //                 'booking' => $booking,
    //                 'catalog_details' => $bookingList,
    //             ];
    //         }

    //         return response()->json([
    //             'data' => $decodedBookings,
    //         ]);
    //     } catch (\Exception $e) {
    //         // Handle the exception
    //         return response()->json(['error' => $e->getMessage()], 500);
    //     }
    // }

    public function bookingHistory()
    {
        // $authUser = auth()->user()->id;
        // $getBooking = Booking::where('provider_id', $authUser)->get();

        // if ($getBooking) {
        //     return ResponseMethod('success', $getBooking);
        // } else {
        //     return ResponseErrorMessage('error', 'Booking data not found');
        // }

        try {
            $authUser = auth()->user()->id;
            $provider = Provider::where('user_id', $authUser)->first();

            if (!$provider) {
                throw new \Exception('Provider not found.');
            }

            $providerId = $provider->id;
            $getBooking = Booking::with('user')->where('provider_id', $providerId)
                ->where('status', 2)
                ->orderBy('id', 'DESC')
                ->paginate(10);

            if ($getBooking->isEmpty()) {
                // throw new \Exception('No booking history found.');
                return response()->json([
                    'status' => 'error',
                    'message' => 'No booking history found.',
                    'data' => []
                ], 200);
            }

            $decodedBookings = [];

            foreach ($getBooking as $booking) {
                $decodedServices = json_decode($booking->service, true);

                if (!is_array($decodedServices)) {
                    throw new \Exception('Error decoding the service JSON.');
                }

                foreach ($decodedServices as $service) {
                    // $catalogIds = explode(',', $service['catalouge_id']);
                    $catalogIds = $service['catalouge_id'];
                    $catalogDetails = [];

                    // foreach ($catalogIds as $catalogId) {
                    $catalog = Catalogue::find($catalogIds);

                    if ($catalog) {
                        $catalogDetails[] = $catalog;
                    }
                    // }

                    $decodedBookings[] = [
                        'booking' => $booking,
                        'service' => $service,
                        'catalog_details' => $catalogDetails,
                    ];
                }
            }

            return response()->json([
                'data' => $decodedBookings,
                'pagination' => [
                    'current_page' => $getBooking->currentPage(),
                    'total_pages' => $getBooking->lastPage(),
                    'per_page' => $getBooking->perPage(),
                    'total' => $getBooking->total(),
                    'next_page_url' => $getBooking->nextPageUrl(),
                    'prev_page_url' => $getBooking->previousPageUrl(),
                ]
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function reviewProvider()
    {
        $authUser = auth()->user()->id;
        $provider = Provider::where('user_id', $authUser)->first();
        $providerId = $provider->id;
        $totalReview = ServiceRating::where('provider_id', $providerId)->count();
        $totalRating = ServiceRating::where('provider_id', $providerId)->sum('rating');

        $avgRating = ($totalReview > 0) ? ServiceRating::where('provider_id', $providerId)->sum('rating') / $totalReview : 0;

        $serviceDetails = ServiceRating::where('provider_id', $providerId)
            ->with(['user:id,name,image'])
            ->paginate(10);

        $allReview = ServiceRating::where('provider_id', $providerId)->get();
        if ($allReview) {
            return response()->json([
                'message' => 'true',
                'total_review' => $totalReview,
                'average_rating' => $avgRating,
                'service_details_with_user' => $serviceDetails,
            ]);
        } else {
            return ResponseErrorMessage('message', 'Booking data not found');
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
}
