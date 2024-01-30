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
            'percentage' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),400);
        }
        $advance_booking = new BookingPercentage();
        $advance_booking->percentage = $request->percentage;
        $advance_booking->save();
        return ResponseMessage('Percentage add Successfully');
    }

    public function percentageGet(){
        $data = BookingPercentage::get();
        if($data->isEmpty()){
            return response()->json(['message' => 'Data Not found'],404);
        }else{
            return response()->json(['data' => $data],200);
        }
    }
    public function percentageUpdate(Request $request){

        $validator = Validator::make($request->all(),[
            'id' => 'required',
            'percentage' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),400);
        }
        $update_percentage = BookingPercentage::find($request->id);
        if(!$update_percentage){
            return response()->json(['message' => 'Data Not found'],404);
        }
        $update_percentage->percentage = $request->percentage;
        $update_percentage->update();
        return response()->json(['data' => $update_percentage],200);
    }

}
