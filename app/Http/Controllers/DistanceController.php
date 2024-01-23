<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Provider;
use App\Models\ServiceRating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistanceController extends Controller
{
    //

    public function findNearestLocation($latitude, $longitude)
    {
        $user_id = auth()->user()->id;
        $user_details = User::where('id', $user_id)->first();
        $salon = Provider::select(DB::raw("*, ( 6371 * acos( cos( radians('$latitude') )
            * cos( radians( latitude ) )
            * cos( radians( longitude ) - radians('$longitude') )
            + sin( radians('$latitude') )
            * sin( radians( latitude ) ) ) ) AS distance"))
            ->havingRaw('distance < 300')
            ->orderBy('distance')
            ->get();
            return ResponseMethod('Nearest Salon Data',$salon);
        }
    public function findNearestLocationByLatLong($latitude,$longitude){
        $salon = Provider::select(DB::raw("*, ( 6371 * acos( cos( radians('$latitude') )
            * cos( radians( latitude ) )
            * cos( radians( longitude ) - radians('$longitude') )
            + sin( radians('$latitude') )
            * sin( radians( latitude ) ) ) ) AS distance"))->havingRaw('distance < 300')->orderBy('distance')
            ->get();
        return ResponseMethod('Nearest Salon Data',$salon);
    }
        public function addressToLatandLong($address){
            $result = app('geocoder')->geocode($address)->get();
            $coordinates = $result[0]->getCoordinates();
            $lat = $coordinates->getLatitude();
            $long = $coordinates->getLongitude();
            return $lat. ' '. $long;
        }

        public function findNearestSalon($latitude ,$longitude, $category, $rating){
            //according to distance, category and rating show data

        }

        public function filter($latitude,$longitude){

//            $topProvider = ServiceRating::orderBy('rating', 'desc')->limit(1)->first();
//            $reviewProviderId = $topProvider->provider_id;
//            $provider = Provider::where('id', $reviewProviderId)->with('providerRating')->first();
//            $ProviderId = $provider->id;
//            $totlaReview = ServiceRating::where('provider_id', $ProviderId)->count();
//            $sumRating = ServiceRating::where('provider_id', $ProviderId)->sum('rating');
//            $avgRating = $sumRating / $totlaReview;
//            return response()->json([
//                'status' => 'success',
//                'provider' => $provider,
//                'review' => $totlaReview,
//                'average rating' => $avgRating
//            ], 200);

            $salon = Provider::select(DB::raw("*, ( 6371 * acos( cos( radians('$latitude') )
            * cos( radians( latitude ) )
            * cos( radians( longitude ) - radians('$longitude') )
            + sin( radians('$latitude') )
            * sin( radians( latitude ) ) ) ) AS distance"))->havingRaw('distance < 300')->orderBy('distance')
                ->get();
            return ResponseMethod('Nearest Salon Data',$salon);



//        $category = Category::all();
//        $rating = ServiceRating::where();
//
//        $reviews = ServiceRating::select('service_ratings.*', 'clients.name as client_name', 'provider.name as provider_name')
//            ->join('services', 'service_ratings.service_id', '=', 'services.id')
//            ->join('providers', 'services.provider_id', '=', 'providers.id')
//            ->join('users as clients', 'service_ratings.user_id', '=', 'clients.id') // Join for client name
//            ->join('users as provider', 'providers.provider_id', '=', 'provider.id') // Join for provider name
//            ->get();
//
//        return $reviews;
//            $salon = Provider::select(DB::raw("*, ( 6371 * acos( cos( radians('$latitude') )
//            * cos( radians( latitude ) )
//            * cos( radians( longitude ) - radians('$longitude') )
//            + sin( radians('$latitude') )
//            * sin( radians( latitude ) ) ) ) AS distance"))->havingRaw('distance < $range')->orderBy('distance')
//                ->get();
//            return ResponseMethod('Nearest Salon Data',$salon);
        }
}
