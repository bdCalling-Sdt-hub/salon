<?php

namespace App\Http\Controllers;

use App\Models\BookingPercentage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PercentageController extends Controller
{
    //

    public function percentageSet(Request $request){

        $validator = Validator::make($request->all(),[
            'appointment_id' => 'required',
            'percentage' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),400);
        }
        $advance_booking = new BookingPercentage();
        $advance_booking->appointment_id = $request->appointment_id;
        $advance_booking->percentage = $request->percentage;
        $advance_booking->save();
        return ResponseMessage('Percentage add Successfully');
    }
}
