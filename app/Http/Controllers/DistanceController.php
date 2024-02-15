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
            ->where('status',1)
            ->get();
        return response()->json([
            'message' => 'Nearest Salon Data',
            'data' => $salon
        ]);
    }

    public function findNearestLocationByLatLong($latitude, $longitude)
    {
        $salon = Provider::select(DB::raw("*, ( 6371 * acos( cos( radians('$latitude') )
            * cos( radians( latitude ) )
            * cos( radians( longitude ) - radians('$longitude') )
            + sin( radians('$latitude') )
            * sin( radians( latitude ) ) ) ) AS distance"))
            ->havingRaw('distance < 300')
            ->orderBy('distance')
            ->get();
        return response()->json([
            'message' => 'Nearest Salon Data',
            'data' => $salon
        ]);
    }

    public function addressToLatandLong($address)
    {
        $result = app('geocoder')->geocode($address)->get();
        $coordinates = $result[0]->getCoordinates();
        $lat = $coordinates->getLatitude();
        $long = $coordinates->getLongitude();
        return $lat . ' ' . $long;
    }

    public function filterOriginal($categoryName, $rating, $distance)
    {
        $authUser = auth()->user()->id;
        $user = User::where('id', $authUser)->first();
        $latitude = $user->latitude;
        $longitude = $user->longitude;

        $query = Provider::select(
            'providers.id',
            'providers.user_id',
            'providers.category_id',
            'providers.business_name',
            'providers.address',
            'providers.description',
            'providers.cover_photo',
            'providers.status',
            'providers.created_at',
            'providers.updated_at',
            'providers.latitude',
            'providers.longitude',
            DB::raw("(6371 * acos( cos( radians('$latitude') )
    * cos( radians( latitude ) )
    * cos( radians( longitude ) - radians('$longitude') )
    + sin( radians('$latitude') )
    * sin( radians( latitude ) ) ) ) AS distance"),
            DB::raw('(SELECT AVG(rating) FROM service_ratings WHERE provider_id = providers.id) AS average_rating')
        )->where('status',1)
            ->join('categories', 'providers.category_id', '=', 'categories.id');

        // Filter by category name
        if ($categoryName) {
            $query->where('categories.category_name', 'LIKE', "%$categoryName%");
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
            return response()->json([
                'message' => 'Salon list',
                'data' => $salons
            ]);
        } else {
            return response()->json([
                'message' => 'No providers found with the given criteria',
                'data' => []
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
                'message' => 'Feature provider',
                'data' => $providers,
            ], 200);
        } else {
            return response()->json([
                'message' => 'No providers found with the given business name',
                'data' => [],
            ], 200);
        }
    }
}
