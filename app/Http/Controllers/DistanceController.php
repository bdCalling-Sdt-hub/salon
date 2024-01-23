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

    public function findNearestLocation($latitude,$longitude){

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

    public function filterOriginal($category, $rating, $distance)
    {
        $authUser = auth()->user()->id;
        $user = User::where('id', $authUser)->first();
        $latitude = $user->latitude;
        $longitude = $user->longitude;

        $query = Provider::select(
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
        );

//        return response()->json([
//            'message' => $query
//        ]);

        // Filter by category
        if ($category) {
            $query->where('category_id', $category);
        }

        // Filter by rating
        if ($rating) {
            $query->havingRaw('average_rating >= ?', [$rating]);
        }

        // Filter by distance
        if ($distance) {
            $query->havingRaw('distance <= ?', [$distance]);
        }

        $salons = $query->orderBy('average_rating', 'desc')->get();

        if ($salons->count() > 0) {
            return ResponseMethod('Filter Providers', $salons);
        } else {
            return response()->json([
                'message' => 'No providers found with the given criteria'
            ]);
        }
    }

    public function searchProvidersBySalon($salon_name = null)
    {
        $authUser = auth()->user()->id;
        $user = User::where('id', $authUser)->first();
        $latitude = $user->latitude;
        $longitude = $user->longitude;

        $providers = Provider::select(
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
            ->where('business_name', 'like', '%' . $salon_name . '%')
            ->orderBy('average_rating', 'desc')
            ->get();

        if ($providers->count() > 0) {
            return response()->json([
                'status' => 'Feature provider',
                'message' => $providers,
            ]);
        } else {
            return response()->json([
                'message' => 'No providers found with the given business name',
            ]);
        }

    }
}
