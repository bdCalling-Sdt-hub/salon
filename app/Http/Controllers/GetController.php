<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Catalogue;
use App\Models\Category;
use App\Models\Payment;
use App\Models\PostBooking;
use App\Models\Provider;
use App\Models\Service;
use App\Models\ServiceRating;
use App\Models\User;
use App\Models\UserPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GetController extends Controller
{
    // salon
    public function salonList(Request $request)
    {
        $perPage = $request->input('per_page', 7);
        $providers = Provider::paginate($perPage);
        if ($providers) {
            return ResponseMethod('Data', $providers);
        } else {
            return ResponseMessage('Provider is empty');
        }
    }

    public function singleSalon($id)
    {
        $singleSalon = Provider::where('id', $id)->first();
        if ($singleSalon) {
            return ResponseMethod('data', $singleSalon);
        } else {
            return ResponseMessage('Salon does not exist');
        }
    }

    // salon owner
    public function getProviderRequest()
    {
        $providerRequest = Provider::with('user')->where('status', 0)->paginate(12);
        if ($providerRequest) {
            return ResponseMethod('Provider Request', $providerRequest);
        } else {
            return ResponseMessage('No Provider Request Found');
        }
    }

    public function approveProviderRequest($id)
    {
        $provider = Provider::with('user')->where('status', 0)->where('id', $id)->first();
        if ($provider) {
            $provider->status = 1;
            $provider->update();
            return ResponseMethod('approve provider request', $provider);
        }
        return ResponseMessage('Provider Request is not pending');
    }

    public function blockProviderRequest($id)
    {
        $providerRequest = Provider::where('status', 0)->where('id', $id)->first();
        if ($providerRequest) {
            $providerRequest->status = 2;
            $providerRequest->update();
            return ResponseMethod('Cancel Provider Successfully', $providerRequest);
        }
        return ResponseMessage('Provider Request does not exist');
    }

    // unblock
    public function providerBlockList()
    {
        $providerBlockList = Provider::with('user')->where('status', 2)->paginate(12);
        if ($providerBlockList) {
            return ResponseMethod('Provider block list', $providerBlockList);
        }
        return ResponseMessage('Block User list is empty');
    }

    public function unblockProvider($id)
    {
        $providerRequest = Provider::with('user')->where('status', 2)->where('id', $id)->first();
        if ($providerRequest) {
            $providerRequest->status = 0;
            $providerRequest->update();
            return ResponseMethod('unblock Provider Successfully', $providerRequest);
        }
        return ResponseMessage('Provider Request does not exist');
    }

    public function cancelProviderRequest($id)
    {
        $providerRequest = Provider::with('user')->where('id', $id)->first();
        if ($providerRequest) {
            $providerRequest->status = 2;
            $providerRequest->update();
            return ResponseMethod('Cancel Provider Successfully', $providerRequest);
        }
        return ResponseMessage('Provider Request does not exist');
    }

    // user list
    public function userList()
    {
        $user = User::where('user_type', 'user')->select(['name', 'email', 'phone_number', 'created_at'])->paginate(9);
        if ($user) {
            return ResponseMethod('User list', $user);
        }
        return ResponseMessage('User is empty');
    }

    //    public function providerList()
    //    {
    //        $user = User::where('user_type', 'provider')->select(['name', 'email', 'phone_number', 'created_at'])->paginate(9);
    //        if ($user) {
    //            return ResponseMethod('Provider list', $user);
    //        }
    //        return ResponseMessage('User is empty');
    //    }

    // public function providerList(Request $request)
    // {
    //     $input = $request->input('input');

    //     $query = User::where('user_type', 'provider');

    //     // Define regular expressions for phone number, email, and string
    //     $phoneNumberRegex = '/^\d{10}$/';  // Matches 10 digits
    //     $emailRegex = '/^\S+@\S+\.\S+$/';  // Matches email format
    //     $stringRegex = '/^[a-zA-Z\s]*$/';  // Matches only letters and spaces

    //     // Validate input against each regex pattern
    //     if (preg_match($phoneNumberRegex, $input)) {
    //         return ResponseMethod('Providers list', $query->where('phone_number', $input));
    //     } elseif (preg_match($emailRegex, $input)) {
    //         return ResponseMethod('Providers list', $query->where('email', 'like', '%' . $input . '%'));
    //     } elseif (preg_match($stringRegex, $input)) {
    //         return ResponseMethod('Providers list', $query->where('name', 'like', '%' . $input . '%'));
    //     } else {
    //         return ResponseMethod('Providers list', 'unknown');
    //     }
    //     $providers = $query->select(['name', 'email', 'phone_number', 'created_at'])->paginate(9);

    //     if ($providers->isEmpty()) {
    //         return ResponseMessage('No providers found');
    //     }
    // }
    public function providerList(Request $request)
    {
        $query = User::where('user_type', 'provider');

        // Search by name, email, or phone number
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($query) use ($searchTerm) {
                $query
                    ->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('phone_number', 'like', '%' . $searchTerm . '%');
            });
        }

        $providers = $query->paginate(9);

        if ($providers->isEmpty()) {
            return ResponseMessage('No providers found');
        }

        return ResponseMethod('Providers list', $providers);
    }

    // single user details

    public function singleUser($id)
    {
        $user = User::where('user_type', 'user')->where('id', $id)->first();
        if ($user) {
            return ResponseMethod('User Data', $user);
        }
        return ResponseMessage('User is empty');
    }

    // block user
    public function deleteUser($id)
    {
        $user = User::find($id);
        $user->delete();
        if (!is_null($user)) {
            return ResponseMessage('User deleted Successfully');
        }
        return ResponseMessage('User does not exist');
    }

    // search provider request
    //    public function searchProviderRequest($name)
    //    {
    //        $query = User::where('user_type', 'provider')->where('user_status', 0);
    //
    //        if ($name) {
    //            $query->where('name', 'like', '%' . $name . '%');
    //        }
    //        $users = $query->get();
    //        return ResponseMethod('provider Request list', $users);
    //    }

    // search provider block
    public function searchProviderBlock($name = null)
    {
        if (!is_null($name)) {
            $block_provider = User::where('user_type', 'provider');
            if (!is_null($block_provider)) {
                $block_provider->where('name', 'like', '%' . $name . '%');
                return ResponseMethod('Block Provider data', $block_provider);
            }
            return ResponseMessage('block provider not found');
        }
        return ResponseMessage('write name which one you want to find');
    }

    public function searchProviderRequest(Request $request)
    {
        $query = User::where('user_type', 'provider')->where('user_status', 0);

        // Search by name
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->input('name') . '%');
        }

        // Search by ID
        if ($request->has('id')) {
            $query->where('id', $request->input('id'));
        }

        // Get paginated results with 9 items per page
        $users = $query->paginate(9);

        if ($users->isEmpty()) {
            return ResponseMessage('No provider requests found');
        }

        return ResponseMethod('Provider request list', $users);
    }

    // search provider
    public function searchProvider(Request $request)
    {
        if (!is_null($name)) {
        }
        $query = Provider::where('user_type', 'provider');
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        $users = $query->get();
        return ResponseMethod('provider data', $users);
    }

    // search user
    public function searchUser($name = null)
    {
        $users = User::where('user_type', 'user')->paginate(9);
        if (!is_null($name)) {
            $user = User::where('user_type', 'user')->where('name', 'like', '%' . $name . '%')->get();

            if ($user->count() > 0) {
                return ResponseMethod('user data', $user);
            }
            return ResponseMessage('user not found');
        }
        return response()->json([
            'message' => 'User List',
            'data' => $users,
        ]);
    }

    // search salon
    public function searchSalon($name = null)
    {
        $barbar = Provider::paginate(9);
        if (!is_null($name)) {
            $salons = Provider::where('business_name', 'like', '%' . $name . '%')->get();

            if ($salons->count() > 0) {
                return ResponseMethod('salon data', $salons);
            }
            return ResponseMessage('salon not found');
        }
        return response()->json(['message' => 'Salon data', 'salons' => $barbar], 200);
    }

    // public function getAppointmentList(Request $request)
    // {
    //     $business_name = $request->business_name;
    //     $client_name = $request->name;

    //     $query = Booking::with('user', 'provider')->whereIn('status', [2, 0, 4]);

    //     if ($business_name !== null) {
    //         $query->whereHas('provider', function ($q) use ($business_name) {
    //             $q->where('business_name', $business_name);
    //         });
    //     }
    //     if ($client_name !== null) {
    //         $query->whereHas('user', function ($q) use ($client_name) {
    //             $q->where('name', $client_name);
    //         });
    //     }
    //     $booking_list = $query->paginate(9);

    //     return response()->json([
    //         'status' => 200,
    //         'message' => 'booking list',
    //         'data' => $booking_list,
    //     ]);
    // }

    public function appointmentListbyId($id)
    {
        //        $booking = Booking::with('user','provider')->paginate(12);
        $booking = Booking::select('bookings.*', 'users.name as client_name', 'providers.business_name as name')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->join('providers', 'bookings.provider_id', '=', 'providers.id')
            ->where('bookings.id', '=', $id)
            ->first();
        if ($booking) {
            return response()->json([
                'status' => 200,
                'message' => 'booking list',
                'data' => $booking,
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Data Not found',
                'data' => 'Not found',
            ]);
        }
    }

    public function getAppointmentList(Request $request)
    {
        $business_name = $request->business_name;
        $client_name = $request->name;

        $query = Booking::with('user', 'provider')->whereIn('status', [2, 0, 4]);

        if ($business_name !== null) {
            $query->whereHas('provider', function ($q) use ($business_name) {
                $q->where('business_name', 'like', '%' . $business_name . '%');
            });
        }

        if ($client_name !== null) {
            $query->whereHas('user', function ($q) use ($client_name) {
                $q->where('name', 'like', '%' . $client_name . '%');
            });
        }

        $booking_list = $query->paginate(9);

        return response()->json([
            'status' => 200,
            'message' => 'booking list',
            'data' => $booking_list,
        ]);
    }

    public function getReviews($providerName = null)
    {
        // Query providers based on the given name if provided
        $providersQuery = $providerName ? Provider::where('business_name', 'like', '%' . $providerName . '%') : Provider::query();

        // Retrieve providers
        $providers = $providersQuery->get();

        // Array to store results for all providers
        $allProviderData = [];

        // Iterate over each provider
        foreach ($providers as $provider) {
            $providerId = $provider->id;

            // Retrieve review data for the current provider
            $totalReview = ServiceRating::where('provider_id', $providerId)->count();
            $avgRating = ($totalReview > 0) ? ServiceRating::where('provider_id', $providerId)->sum('rating') / $totalReview : 0;

            $serviceDetails = ServiceRating::where('provider_id', $providerId)
                ->with(['user:id,name,image'])
                ->get();

            // Store data for the current provider
            $providerData = [
                'salon' => $provider,
                'total_review' => $totalReview,
                'average_rating' => $avgRating,
                'service_details_with_user' => $serviceDetails,
            ];

            // Add data to the array
            $allProviderData[] = $providerData;
        }

        // Check if any data was found for any provider
        if (!empty($allProviderData)) {
            return response()->json([
                'message' => 'true',
                'providers_data' => $allProviderData,
            ]);
        } else {
            return response()->json([
                'message' => 'No provider data found'
            ], 404);
        }
    }

    public function getReviewsByProviderId($providerId)
    {
        $provider = Provider::where('id', $providerId)->first();
        $totalReview = ServiceRating::where('provider_id', $providerId)->count();
        $avgRating = ($totalReview > 0) ? ServiceRating::where('provider_id', $providerId)->sum('rating') / $totalReview : 0;

        $serviceDetails = ServiceRating::where('provider_id', $providerId)
            ->with(['user:id,name,image'])
            ->get();

        $allReview = ServiceRating::where('provider_id', $providerId)->get();
        if ($allReview) {
            return response()->json([
                'message' => 'true',
                'salon' => $provider,
                'total_review' => $totalReview,
                'average_rating' => $avgRating,
                'service_details_with_user' => $serviceDetails,
            ]);
        } else {
            return response()->json([
                'message' => 'Provider data not found'
            ], 404);
        }
    }

    // review average rating

    public function averageReviewRating($providerId)
    {
        $reviews = ServiceRating::select('service_ratings.*', 'clients.name as client_name', 'provider.name as provider_name')
            ->join('services', 'service_ratings.service_id', '=', 'services.id')
            ->join('providers', 'services.provider_id', '=', 'providers.id')
            ->join('users as clients', 'service_ratings.user_id', '=', 'clients.id')  // Join for client name
            ->join('users as provider', 'providers.provider_id', '=', 'provider.id')  // Join for provider name
            ->where('provider.id', '=', $providerId)  // Filter by provider ID
            ->get();
        return $reviews;
    }

    public function test($latitude, $longitude)
    {
        $salon = Provider::select(DB::raw("*, ( 6371 * acos( cos( radians('$latitude') )
            * cos( radians( latitude ) )
            * cos( radians( longitude ) - radians('$longitude') )
            + sin( radians('$latitude') )
            * sin( radians( latitude ) ) ) ) AS distance"))
            ->havingRaw('distance < 300')
            ->orderBy('distance')
            ->get();
        return ResponseMethod('Nearest Salon Data', $salon);
    }

    public function bookingHistory()
    {
        $booking_history = Payment::all();
        if ($booking_history) {
            return response()->json([
                'status' => 'success',
                'data' => $booking_history,
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'Booking history is empty'
            ]);
        }
    }

    public function filter($category_name = null)
    {
        if (!is_null($category_name)) {
            $salon = Provider::select('providers.*')
                ->Join('categories', 'providers.category_id', '=', 'categories.id')
                ->where('category_name', 'like', '%' . $category_name . '%')
                ->get();
            if ($salon->count() > 0) {
                return ResponseMethod('Salon data', $salon);
            }

            return ResponseMessage('Salon not found');
        }

        return ResponseMessage('Provide category name for search');
    }

    public function paymentHistory()
    {
        $payment_history = Payment::with('user', 'package')->paginate(9);
        if ($payment_history) {
            $gold_count = Payment::where('package_id', 1)->count();
            $diamond_count = Payment::where('package_id', 1)->count();
            $platinum_count = Payment::where('package_id', 1)->count();
            $package_count = [
                'gold_count' => $gold_count,
                'diamond_count' => $diamond_count,
                'platinum_count' => $platinum_count,
            ];
            return response()->json([
                'package_count' => $package_count,
                'data' => $payment_history,
            ]);
        }
        return response()->json([
            'status' => 404,
            'message' => 'No data found'
        ]);
    }

    public function paymentHistoryById($id)
    {
        $payment_history = Payment::with('user', 'package')->where('id', $id)->first();
        if ($payment_history) {
            return response()->json([
                'status' => 200,
                'data' => $payment_history,
            ]);
        }
        return response()->json([
            'status' => 404,
            'message' => 'No data found'
        ]);
    }

    public function paymentHistoryUser()
    {
        $payment_history = UserPayment::with('user', 'booking')->paginate(9);
        if ($payment_history) {
            return response()->json([
                'status' => 200,
                'data' => $payment_history,
            ]);
        }
        return response()->json([
            'status' => 404,
            'message' => 'No data found'
        ]);
    }

    public function paymentHistoryByIdUser($id)
    {
        $payment_history = UserPayment::with('user', 'booking')->where('id', $id)->first();
        if ($payment_history) {
            return response()->json([
                'status' => 200,
                'data' => $payment_history,
            ]);
        }
        return response()->json([
            'status' => 404,
            'message' => 'No data found'
        ]);
    }

    // appointment booking
    public function appointmentBooking($id)
    {
        //        $catalogues = Catalogue::with('provider', 'service')->where('provider_id',$id)->get();
        //        $providerData = [];
        //        foreach ($catalogues as $catalogue) {
        //            $providerId = $catalogue->provider->id;
        //            if (!isset($providerData[$providerId])) {
        //                $providerData[$providerId] = [
        //                    'provider' => $catalogue->provider,
        //                    'services' => [],
        //                    'catalogs' => [],
        //                ];
        //            }
        //            $providerData[$providerId]['services'][] = $catalogue->service;
        //            $providerData[$providerId]['catalogs'][] = $catalogue;
        //        }
        //
        //        $providerData = array_values($providerData);
        //
        //        return response()->json([
        //            'message' => 'true',
        //            'provider_data' => $providerData,
        //        ]);
        $catalogues = Catalogue::with('provider', 'service')->where('provider_id', $id)->get();
        $providerData = [];

        foreach ($catalogues as $catalogue) {
            $providerId = $catalogue->provider->id;

            if (!isset($providerData[$providerId])) {
                $providerData[$providerId] = [
                    'provider' => json_decode($catalogue->provider->toJson(), true),
                    'services' => [],
                    'catalogs' => [],
                ];
            }

            // For each service, include the available_service_our field
            $service = $catalogue->service;
            $service->available_service_our = json_decode($service->available_service_our, true);

            $providerData[$providerId]['services'][] = $service;

            // For each catalog, include the available_service_our field as a JSON string
            $catalog = $catalogue->toArray();

            $catalog['service']['available_service_our'] = json_encode($catalog['service']['available_service_our'], true);
            $catalog['provider']['available_service_our'] = json_encode($catalog['provider']['available_service_our'], true);

            // Convert available_service_our back to JSON format
            $catalog['provider']['available_service_our'] = json_decode($catalog['provider']['available_service_our']);
            $catalog['service']['available_service_our'] = json_decode($catalog['service']['available_service_our']);

            $providerData[$providerId]['catalogs'][] = $catalog;
        }

        $providerData = array_values($providerData);

        return response()->json([
            'message' => 'true',
            'provider_data' => $providerData,
        ]);
    }

    public function getBooking(Request $request)
    {
        try {
            $check_user = auth()->user();
            $booking_history = Booking::first();

            if (!$booking_history) {
                throw new \Exception('No booking history found.');
            }

            $provider_id = $booking_history->salon_id;
            $salon = null;

            if ($provider_id) {
                $salon = Provider::find($provider_id);
            }

            $services = json_decode($booking_history->service);
            $catalog_details = [];
            $service_details = [];

            if (!$services) {
                throw new \Exception('No services found in the booking.');
            }

            foreach ($services as $service) {
                $catalogIds = $service->catalouge_id;  // Assuming this is the correct key in your data
                $serviceIds = (array) $service->service_id;  // Ensure $serviceIds is always an array

                if (!$catalogIds || !$serviceIds) {
                    continue;  // Skip this iteration if either catalogIds or serviceIds is empty
                }

                foreach ($catalogIds as $catalogId) {
                    // Retrieve catalog details for each catalog ID
                    $catalog_info = Catalogue::find($catalogId);
                    if ($catalog_info) {
                        $catalog_details[] = $catalog_info;
                    }
                }
                foreach ($serviceIds as $serviceId) {
                    // Retrieve service details for each service ID
                    $service_info = Service::find($serviceId);
                    if ($service_info) {
                        $service_details[] = $service_info;
                    }
                }
            }

            return response()->json([
                'service' => $service_details,
                'catalogue' => $catalog_details,
                'user' => $check_user,
                'provider' => $salon,
            ]);
        } catch (\Exception $e) {
            // Handle the exception
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
