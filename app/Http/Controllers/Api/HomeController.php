<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Catalogue;
use App\Models\Category;
use App\Models\Provider;
use App\Models\Service;
use App\Models\ServiceRating;
use App\Models\User;
use Illuminate\Http\Request;
use DB;

class HomeController extends Controller
{
    public function salounList($id)
    {
        $saloun = Provider::where('category_id', $id)->get();
        if ($saloun) {
            return ResponseMethod('success', $saloun);
        } else {
            return ResponseErrorMethod('error', 'Provider data not found');
        }
    }

    public function salounService($id)
    {
        $salounService = Service::where('provider_id', $id)->get();
        if ($salounService) {
            return ResponseMethod('success', $salounService);
        } else {
            return ResponseErrorMethod('error', 'Provider service not found');
        }
    }

    public function serviceDetails($id)
    {
        return Service::where('id', $id)->with('ServiceRating')->get();
    }

    public function selonDetails($id)
    {
        return Provider::where('id', $id)->with('salonDetails', 'providerRating')->get();
    }

    public function catalouge($id)
    {
        $showCatloug = Catalogue::where('service_id', $id)->get();

        if ($showCatloug) {
            return ResponseMethod('success', $showCatloug);
        } else {
            return ResponseErrorMethod('error', 'This service catalouge not found');
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
        ]);
    }

    public function bookingAppoinment($id)
    {
        return $appoinmentsData = Provider::where('id', $id)->with('salonDetails')->get();
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
            ]);
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
                'service' => $request->input('service'),
                'price' => $request->input('price'),
                'date' => $request->input('date'),
                'time' => $request->input('time'),
            ]);

            if ($post_booking) {
                return ResponseMethod('success', 'Booking success');
            } else {
                return ResponseErrorMessage('error', 'Booking faile');
            }
        }
    }

    public function providerApproval()
    {
        $authUser = auth()->user()->id;
        if ($authUser) {
            $bookingDetails = Booking::where('user_id', $authUser)->first();
            $providerId = $bookingDetails->provider_id;
            $bookingStatus = $bookingDetails->status;
            if ($bookingStatus == 0) {
                return response()->json([
                    'status' => 'pending',
                    'message' => 'pending your request'
                ]);
            } else {
                $providerInfo = Provider::where('id', $providerId)->first();
                $userInfo = $bookingDetails->user_id;
                $userDetails = User::where('id', $userInfo)->first();
                return response()->json([
                    'status' => 'success',
                    'provider' => $providerInfo,
                    'userDetails' => $userDetails,
                    'bookingHistory' => $bookingDetails
                ]);
            }
        }
    }

    public function appoinments($id)
    {
        $authUser = auth()->user()->id;

        if ($authUser) {
            $appoinments = Booking::where('provider_id', $id)->get();
            if ($appoinments) {
                return ResponseMethod('success', $appoinments);
            } else {
                return ResponseErrorMessage('error', 'You hav no record found');
            }
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
                    return ResponseMethod('success', 'Booking update success');
                } else {
                    return ResponseErrorMessage('false', 'Booking update faile');
                }
            }
        }
    }

    public function bookingDetails($id)
    {
        $authUser = auth()->user();
        $authId = $authUser->id;
        if ($authUser) {
            $bookingDetails = Booking::where('id', $id)->first();
            if ($bookingDetails) {
                return response()->json([
                    'status' => 'success',
                    'booking details' => $bookingDetails
                ]);
            } else {
                return ResponseErrorMessage('error', 'Data not found');
            }
        }
    }

    public function userHome()
    {
        $topProvider = ServiceRating::orderBy('rating', 'desc')->limit(1)->first();
        $reviewProviderId = $topProvider->provider_id;
        $provider = Provider::where('id', $reviewProviderId)->with('providerRating')->first();
        $ProviderId = $provider->id;
        $totlaReview = ServiceRating::where('provider_id', $ProviderId)->count();
        $sumRating = ServiceRating::where('provider_id', $ProviderId)->sum('rating');
        $avgRating = $sumRating / $totlaReview;
        return response()->json([
            'status' => 'success',
            'provider' => $provider,
            'review' => $totlaReview,
            'average rating' => $avgRating
        ]);
    }

    public function searchCategory(Request $request)
    {
        $catName = $request->categoryName;
        $findCategory = Category::where('category_name', 'like', '%' . $catName . '%')->get();
        if ($findCategory) {
            return response()->json([
                'status' => 'success',
                'searcgResult' => $findCategory
            ]);
        } else {
            return ResponseErrorMessage('error', 'Search data not found');
        }
    }
}
