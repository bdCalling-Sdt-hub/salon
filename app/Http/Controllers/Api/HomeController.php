<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\BookingPercentage;
use App\Models\Catalogue;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
use App\Models\ServiceRating;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use DB;

class HomeController extends Controller
{
    public function read_at() {}

    public function markRead(Request $request)
    {
        $user = auth()->user();

        if ($user) {
            $notifications = $user->notifications()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'status' => 'success',
                'notifications' => $notifications,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }
    }

    public function readNotification()
    {
        $user = auth()->user();

        if ($user) {
            // Mark all unread notifications as read
            $user->unreadNotifications->markAsRead();

            // Retrieve and return the updated notifications
            $notifications = $user->notifications;

            return response()->json([
                'status' => 'success',
                'notifications' => $notifications,
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'User not authenticated',
            ], 401);
        }
    }

    public function salounList($id)
    {
        $authUser = auth()->user()->id;
        $user = User::where('id', $authUser)->first();
        $latitude = $user->latitude;
        $longitude = $user->longitude;

        $providers = Provider::where('category_id', $id)->get();

        $providerData = [];

        foreach ($providers as $provider) {
            $providerId = $provider->id;

            $totalReview = ServiceRating::where('provider_id', $providerId)->count();
            $sumRating = ServiceRating::where('provider_id', $providerId)->sum('rating');
            $avgRating = ($totalReview > 0) ? $sumRating / $totalReview : 0;

            $salons = Provider::select(
                'id',
                'user_id',
                'category_id',
                'business_name',
                'address',
                'description',
                'cover_photo',
                'status',
                'created_at',
                'updated_at',
                'latitude',
                'longitude',
                DB::raw("(6371 * acos( cos( radians('$latitude') )
                * cos( radians( latitude ) )
                * cos( radians( longitude ) - radians('$longitude') )
                + sin( radians('$latitude') )
                * sin( radians( latitude ) ) ) ) AS distance")
            )
                ->where('id', $providerId)  // Add this condition to get details for the specific provider
                ->first();

            if ($salons) {
                $providerData[] = [
                    'id' => $providerId,
                    'avg_rating' => $avgRating,
                    'distance' => $salons->distance,
                    'provider_details' => $salons
                ];
            }
        }

        if (!empty($providerData)) {
            return response()->json([
                'status' => 'success',
                'provider_data' => $providerData
            ]);
        } else {
            return ResponseErrorMessage('error', 'Provider data not found');
        }
    }

    public function salounService($id)
    {
        $salounServices = Service::where('provider_id', $id)->get([
            'id', 'category_id', 'provider_id', 'service_name', 'gallary_photo', 'service_duration',
            'salon_service_charge', 'home_service_charge'
        ]);

        $serviceData = [];
        $totalServiceRating = 0;
        $totalServiceReview = 0;

        foreach ($salounServices as $service) {
            $serviceId = $service->id;

            $serviceRating = ServiceRating::where('service_id', $serviceId)->sum('rating');
            $serviceReview = ServiceRating::where('service_id', $serviceId)->count();

            $avgServiceRating = ($serviceReview > 0) ? $serviceRating / $serviceReview : 0;

            $service['gallary_photo'] = json_decode($service['gallary_photo'], true);

            $serviceData[] = [
                'service_id' => $serviceId,
                'service_name' => $service->service_name,  // Replace with your actual column name
                'avg_service_rating' => $avgServiceRating,
                'service_details' => $service,
            ];

            $totalServiceRating += $serviceRating;
            $totalServiceReview += $serviceReview;
        }

        $avgOverallRating = ($totalServiceReview > 0) ? $totalServiceRating / $totalServiceReview : 0;

        if ($salounServices->isNotEmpty()) {
            return response()->json([
                'status' => 'success',
                'avg_overall_rating' => $avgOverallRating,
                'services' => $serviceData
            ]);
        } else {
            return ResponseErrorMessage('error', 'Provider services not found');
        }
    }

    public function serviceDetails($id)
    {
        $totalReview = ServiceRating::where('service_id', $id)->count();
        $sumRating = ServiceRating::where('service_id', $id)->sum('rating');

        $avgRating = ($totalReview > 0) ? ServiceRating::where('service_id', $id)->sum('rating') / $totalReview : 0;

        $serviceDetails = Service::where('id', $id)
            ->with(['ServiceRating.user:id,name,image'])
            ->first();

        if ($serviceDetails) {
            $decodedData = [
                'id' => $serviceDetails->id,
                'category_id' => $serviceDetails->category_id,
                'provider_id' => $serviceDetails->provider_id,
                'service_name' => $serviceDetails->service_name,
                'service_description' => $serviceDetails->service_description,
                // ... (add other attributes as needed)
                'gallary_photo' => json_decode($serviceDetails->gallary_photo, true),
                'service_duration' => $serviceDetails->service_duration,
                'salon_service_charge' => $serviceDetails->salon_service_charge,
                'home_service_charge' => $serviceDetails->home_service_charge,
                'set_booking_mony' => $serviceDetails->set_booking_mony,
                'available_service_our' => json_decode($serviceDetails->available_service_our, true),
                'created_at' => $serviceDetails->created_at,
                'updated_at' => $serviceDetails->updated_at,
                'service_rating' => $serviceDetails->ServiceRating->map(function ($rating) {
                    $user = $rating->user;
                    return [
                        'id' => $rating->id,
                        'user_id' => $user->id,
                        'user_name' => $user->name,
                        'user_image' => $user->image,  // Assuming the User model has an 'image' attribute
                        'review' => $rating->review,
                        'rating' => $rating->rating,
                    ];
                }),
            ];

            return response()->json([
                'message' => 'success',
                'review' => $totalReview,
                'rating' => $avgRating,
                'service_details' => $decodedData
            ], 200);
        } else {
            return ResponseErrorMessage('error', 'Provider service not found');
        }
    }

    public function selonDetails($id)
    {
        $totalReview = ServiceRating::where('provider_id', $id)->count();
        $sumRating = ServiceRating::where('provider_id', $id)->sum('rating');
        $avgRating = ($totalReview > 0) ? ServiceRating::where('provider_id', $id)->sum('rating') / $totalReview : 0;

        $selonDetails = Provider::where('id', $id)->with('salonDetails', 'providerRating.user')->first();

        if ($selonDetails) {
            $decodedData = $selonDetails->toArray();

            $decodedData['available_service_our'] = json_decode($decodedData['available_service_our'], true);
            $decodedData['gallary_photo'] = json_decode($decodedData['gallary_photo'], true);

            foreach ($decodedData['salon_details'] as &$salonDetail) {
                $salonDetail['available_service_our'] = json_decode($salonDetail['available_service_our'], true);
                $salonDetail['gallary_photo'] = json_decode($salonDetail['gallary_photo'], true);
            }

            $providerRatings = $decodedData['provider_rating'];
            foreach ($providerRatings as &$rating) {
                $rating['user_name'] = $rating['user']['name'];
                $rating['user_image'] = $rating['user']['image'];
                unset($rating['user']);
            }

            return response()->json([
                'status' => 'success',
                'total_review' => $totalReview,
                'avg_rating' => $avgRating,
                'selon_details' => $decodedData
            ], 200);
        } else {
            return ResponseErrorMessage('error', 'Provider service not found');
        }
    }

    public function catalouge($id)
    {
        $catalogues = Catalogue::where('service_id', $id)->get([
            'id', 'provider_id', 'catalog_name', 'image', 'service_duration',
            'salon_service_charge', 'home_service_charge'
        ]);

        if ($catalogues->isNotEmpty()) {
            $catalogueRatings = [];

            foreach ($catalogues as $catalogue) {
                $catalogueId = $catalogue->id;
                $totalReview = ServiceRating::where('catalogue_id', $catalogueId)->count();
                $sumRating = ServiceRating::where('catalogue_id', $catalogueId)->sum('rating');

                $avgRating = ($totalReview > 0) ? $sumRating / $totalReview : 0;

                $catalogueRatings[] = [
                    'catalogue_id' => $catalogueId,
                    'avg_rating' => $avgRating,
                    'catalogue_details' => $catalogue,
                ];
            }

            return response()->json([
                'status' => 'success',
                'catalogue_ratings' => $catalogueRatings,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'No catalogues found for this service',
            ], 404);
        }
    }

    public function catalougeDetails($id)
    {
        $totalReview = ServiceRating::where('catalogue_id', $id)->count();
        $sumRating = ServiceRating::where('catalogue_id', $id)->sum('rating');
        $avgRating = ($totalReview > 0) ? $sumRating / $totalReview : 0;

        $catalougeDetails = Catalogue::where('id', $id)->with('serviceRating.user:id,name,image')->first();

        if ($catalougeDetails) {
            $catalougeDetailsArray = $catalougeDetails->toArray();
            $catalougeDetailsArray['service_hour'] = json_decode($catalougeDetailsArray['service_hour'], true);
            $catalougeDetailsArray['image'] = json_decode($catalougeDetailsArray['image'], true);

            $catalougeRatings = $catalougeDetailsArray['service_rating'];

            foreach ($catalougeRatings as &$rating) {
                $rating['user_name'] = $rating['user']['name'];
                $rating['user_image'] = $rating['user']['image'];
                unset($rating['user']);
            }

            return response()->json([
                'message' => 'success',
                'review' => $totalReview,
                'rating' => $avgRating,
                'cataloug_details' => $catalougeDetailsArray
            ], 200);
        } else {
            return response()->json([
                'message' => 'error',
                'error' => 'Catalogue not found',
            ], 404);
        }
    }

    public function bookingAppoinment($id)
    {
        $provider = Provider::where('id', $id)->with('services.catalog')->first();

        if (!$provider) {
            return response()->json([
                'status' => 'error',
                'message' => 'Provider not found',
            ], 404);
        }

        // Decode JSON fields in the Provider
        $decodedProvider = $provider->toArray();
        $decodedProvider['available_service_our'] = json_decode($provider->available_service_our, true);
        $decodedProvider['gallary_photo'] = json_decode($provider->gallary_photo, true);

        // Manually handle services to replace null catalog with an empty array
        $decodedProvider['services'] = collect($provider->services)->map(function ($service) {
            $serviceArray = $service->toArray();
            $serviceArray['catalog'] = $service->catalog ?? [];  // Use the null coalescing operator (??) here
            return $serviceArray;
        })->all();

        return response()->json([
            'status' => 'success',
            'provider' => $decodedProvider,
        ], 200);
    }

    public function bookingSummary()
    {
        $authUser = auth()->user()->id;
        if ($authUser) {
            $bookingDetails = Booking::where('user_id', $authUser)->first();
            $providerId = $bookingDetails->provider_id;
            $providerInfo = Provider::where('id', $providerId)->first();
            $userInfo = $bookingDetails->user_id;
            $userDetails = User::where('id', $userInfo)->first();
            return response()->json([
                'status' => 'success',
                'provider' => $providerInfo,
                'userDetails' => $userDetails,
                'bookingHistory' => $bookingDetails
            ], 200);
        } else {
            return response()->json([
                'status' => 'false',
                'user unauthenticate'
            ], 401);
        }
    }

    public function postBooking(BookingRequest $request)
    {
        $authUser = auth()->user();
        $authId = $authUser->id;
        if ($authUser) {
            $post_booking = Booking::create([
                'user_id' => $authId,
                'provider_id' => $request->input('providerId'),
                'service_id' => $request->input('serviceId'),
                'catalogue_id' => $request->input('catalougeId'),
                'service_duration' => $request->input('serviceDuration'),
                'service_type' => $request->input('serviceType'),
                'service' => $request->input('service'),
                'price' => $request->input('price'),
                'date' => $request->input('date'),
                'time' => $request->input('time'),
            ]);

            if ($post_booking) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Booking success',
                    'Notification' => sendNotification('Send your booking request', $post_booking),
                ], 200);
            } else {
                return ResponseErrorMessage('error', 'Booking faile');
            }
        }
    }

    public function providerApproval()
    {
        try {
            $authUser = auth()->user()->id;

            $getBooking = Booking::where('user_id', $authUser)->with('user')->get();

            if ($getBooking->isEmpty()) {
                throw new \Exception('No booking history found.');
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
                'decoded_bookings' => $decodedBookings,
            ]);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function appoinments()
    {
        try {
            $authUser = auth()->user()->id;

            $getBooking = Booking::where('user_id', $authUser)->with('Provider')->get();

            if ($getBooking->isEmpty()) {
                throw new \Exception('No booking history found.');
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
                'decoded_bookings' => $decodedBookings,
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function appoinmentHistory()
    {
        try {
            $authUser = auth()->user()->id;

            $getBooking = Booking::where('user_id', $authUser)->with('Provider')->get();

            if ($getBooking->isEmpty()) {
                throw new \Exception('No booking history found.');
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
                'decoded_bookings' => $decodedBookings,
            ], 200);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function bookingCancel($id)
    {
        $authUser = auth()->user();
        if ($authUser) {
            $deleteBooking = Booking::where('id', $id)->delete();
            if ($deleteBooking) {
                return ResponseMethod('success', 'Booking delete success');
            } else {
                return ResponseErrorMessage('error', 'Booking delete fails');
            }
        }
    }

    public function reSchedule(Request $request)
    {
        $authUser = auth()->user();
        if ($authUser) {
            $date = $request->date;
            $time = $request->time;
            $scedulCheck = Booking::where('date', $date)->where('time', $time)->count();
            if ($scedulCheck) {
                return ResponseErrorMessage('false', 'Sloat not avlable');
            } else {
                $updateBooking = Booking::find($request->id);
                $updateBooking->id = $request->id;
                $updateBooking->date = $request->date;
                $updateBooking->time = $request->time;
                $updateBooking->save();
                if ($updateBooking) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'update schedule success',
                        'Notification' => sendNotification('user booking re-shedule', $updateBooking),
                    ], 200);
                    ResponseMethod('success', 'Booking update success');
                } else {
                    return ResponseErrorMessage('false', 'Booking update faile');
                }
            }
        }
    }

    public function bookingDetails($id)
    {
        try {
            $authUser = auth()->user()->id;

            $booking = Booking::where('user_id', $authUser)->where('id', $id)->with('user', 'Provider')->first();

            if (!$booking) {
                throw new \Exception('Booking not found.');
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

    public function userHome()
    {
        $authUser = auth()->user()->id;
        $user = User::where('id', $authUser)->first();
        $latitude = $user->latitude;
        $longitude = $user->longitude;
        $provider = Provider::get();
        if ($provider) {
            $salons = Provider::select(
                'id',
                'user_id',
                'category_id',
                'business_name',
                'address',
                'description',
                'cover_photo',
                'status',
                'created_at',
                'updated_at',
                'latitude',
                'longitude',
                DB::raw("(6371 * acos( cos( radians('$latitude') )
            * cos( radians( latitude ) )
            * cos( radians( longitude ) - radians('$longitude') )
            + sin( radians('$latitude') )
            * sin( radians( latitude ) ) ) ) AS distance"),
                DB::raw('(SELECT AVG(rating) FROM service_ratings WHERE provider_id = providers.id) AS average_rating')
            )
                ->havingRaw('distance < 10000')
                ->orderBy('average_rating', 'desc')
                ->get();

            return ResponseMethod('Featcer provider', $salons);
        } else {
            return ResponseErrorMessage('error', 'Provider not found');
        }
        // $providers = Provider::with('providerRating')
        //     ->withAvg('providerRating', 'rating')
        //     ->orderByDesc('provider_rating_avg_rating')
        //     ->get();

        // $decodedData = [];
        // foreach ($providers->toArray() as $item) {
        //     $item['available_service_our'] = json_decode($item['available_service_our'], true);
        //     $item['gallary_photo'] = json_decode($item['gallary_photo'], true);

        //     // Loop through salon_details and decode available_service_our for each item
        //     // foreach ($item['salon_details'] as &$salonDetail) {
        //     //     $salonDetail['available_service_our'] = json_decode($salonDetail['available_service_our'], true);
        //     //     $salonDetail['gallary_photo'] = json_decode($salonDetail['gallary_photo'], true);
        //     // }

        //     $decodedData[] = $item;  // Add the updated item to the new array
        // }
        // $lat = auth()->user()->latitude;
        // $long = auth()->user()->longitude;
        // return response()->json([
        //     'status' => 'success',
        //     'message' => $decodedData,
        //     'distance' => $this->findNearestLocation($lat, $long),
        // ]);

        // $decodedData = [];

        // if ($provider) {
        //     $provider['available_service_our'] = json_decode($provider['available_service_our'], true);
        //     $provider['gallary_photo'] = json_decode($provider['gallary_photo'], true);

        //     $decodedData[] = $provider;
        // }

        // $ProviderId = $provider->id;
        // $totlaReview = ServiceRating::where('provider_id', $ProviderId)->count();
        // $sumRating = ServiceRating::where('provider_id', $ProviderId)->sum('rating');
        // $avgRating = $sumRating / $totlaReview;
        // return response()->json([
        //     'status' => 'success',
        //     'provider' => $decodedData,
        //     'review' => $totlaReview,
        //     'average rating' => $avgRating
        // ], 200);
    }

    public function searchCategory(Request $request)
    {
        $catName = $request->categoryName;
        $findCategory = Category::where('category_name', 'like', '%' . $catName . '%')->first();
        $categoryId = $findCategory->id;
        $provider = Provider::where('category_id', $categoryId)->get();

        if ($findCategory) {
            return response()->json([
                'status' => 'success',
                'searcgResult' => $provider
            ], 200);
        } else {
            return ResponseErrorMessage('error', 'Search data not found');
        }
    }

    //  find nearest location
    public function nearbyProviders()
    {
        $authUser = auth()->user()->id;
        $user = User::where('id', $authUser)->first();
        $latitude = $user->latitude;
        $longitude = $user->longitude;
        $provider = Provider::get();
        if ($provider) {
            $salons = Provider::select(
                'id',
                'user_id',
                'category_id',
                'business_name',
                'address',
                'description',
                'cover_photo',
                'status',
                'created_at',
                'updated_at',
                'latitude',
                'longitude',
                DB::raw("(6371 * acos( cos( radians('$latitude') )
            * cos( radians( latitude ) )
            * cos( radians( longitude ) - radians('$longitude') )
            + sin( radians('$latitude') )
            * sin( radians( latitude ) ) ) ) AS distance"),
                DB::raw('(SELECT AVG(rating) FROM service_ratings WHERE provider_id = providers.id) AS average_rating')
            )
                ->havingRaw('distance < 10000')
                ->orderBy('distance')
                ->get();

            return ResponseMethod('Nearest Salon Data', $salons);
        } else {
            return ResponseErrorMessage('error', 'Provider not found');
        }
    }
}
