<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function bookingComplete(){

        $user_count = Booking::where('status',2)->count();
        if($user_count){
            return response()->json([
                'status' => true,
                'booking completed' => $user_count,
            ]);
        }
        return response()->json([
            'message' => 'No data found'
        ]);
    }

    public function bookingCancel(){

        $user_count = Booking::where('status',4)->count();
        if($user_count){
            return response()->json([
                'status' => true,
                'booking Cancel' => $user_count,
            ]);
        }
        return response()->json([
            'message' => 'No data found'
        ]);
    }


    public function bookingPending(){

        $user_count = Booking::where('status',0)->count();
        if($user_count){
            return response()->json([
                'status' => true,
                'booking Pending' => $user_count,
            ]);
        }
        return response()->json([
            'message' => 'No data found'
        ]);
    }


}
