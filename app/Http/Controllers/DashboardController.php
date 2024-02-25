<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function bookingComplete()
    {
        $total_booking = Booking::all()->count();
        $booking_complete = Booking::where('status', 2)->count();
        $booking_cancel = Booking::where('status', 4)->count();
        $booking_pending = Booking::where('status', 0)->count();
        if ($booking_complete) {
            return response()->json([
                'booking_completed' => $booking_complete,
                'booking_cancel' => $booking_cancel,
                'booking_pending' => $booking_pending,
                'total_booking' => $total_booking,
            ]);
        }
        return response()->json([
            'message' => 'No data found'
        ]);
    }

    public function bookingCancel()
    {
        $user_count = Booking::where('status', 4)->count();
        if ($user_count) {
            return response()->json([
                'status' => true,
                'booking Cancel' => $user_count,
            ]);
        }
        return response()->json([
            'message' => 'No data found'
        ]);
    }

    public function bookingPending()
    {
        $user_count = Booking::where('status', 0)->count();
        if ($user_count) {
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
