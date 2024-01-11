<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DistanceController extends Controller
{
    //

    public function findNearestLocation(){
        $user_id = auth()->user()->id;
        $user_details = User::where('id',$user_id)->first();
        $user_address = $user_details->address;
        $lat_long = $this->addressToLatandLong($user_address);
        list($latitude, $longitude) = explode(' ', $lat_long);
        $salon = Provider::select(DB::raw("*, ( 6371 * acos( cos( radians('$latitude') )
            * cos( radians( latitude ) )
            * cos( radians( longitude ) - radians('$longitude') )
            + sin( radians('$latitude') )
            * sin( radians( latitude ) ) ) ) AS distance"))->havingRaw('distance < 300')->orderBy('distance')
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
}
