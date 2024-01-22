<?php

namespace App\Http\Controllers;

use App\Models\Provider;
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
        return ResponseMethod('Nearest Salon Data', $salon);
    }
}
