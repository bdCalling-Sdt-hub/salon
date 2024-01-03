<?php

namespace App\Http\Controllers;

use App\Models\Onboard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OnboardController extends Controller
{

    public function showOnboard(){
        $onboard = Onboard::all();
        return ResponseMethod('Onboard list',$onboard);
    }

    public function showSingleOnboard($id){
        $onboard = Onboard::where('id',$id)->first();
        if($onboard){
            return ResponseMethod('Onboard',$onboard);
        }
        else{
            return ResponseMessage('Onboard Not Exist');
        }
    }

    public function addOnboard(Request $request){

        $validator = Validator::make($request->all(),[
            'onboard_image' => 'required|mimes:jpg,png,jpeg,gif,svg|max:2048',
            'onboard_title' => 'required|string',
            'onboard_description' => 'string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),400);
        }
        $onboard = new Onboard();
        if ($request->file('onboard_image')){
            $onboard->onboard_image = $this->saveImage($request);
        }
        $onboard->onboard_title = $request->onboard_title;
        $onboard->onboard_description = $request->onboard_description;
        $onboard->save();
        return ResponseMethod('Onboard add successfully',$onboard);
    }
    public function deleteOnboard($id){
        $onboard = Onboard::where('id',$id)->first();
        if ($onboard){
            $onboard->delete();
            return ResponseMessage('Onboard delete successfully');
        }else{
            return ResponseMessage('Onboard Not Found');
        }
    }
    protected function saveImage($request){
        $image = $request->file('onboard_image');
        $imageName = rand() . '.' . $image->getClientOriginalExtension();
        $directory = 'adminAsset/onboard_image/';
        $imgUrl = $directory . $imageName;
        $image->move($directory, $imageName);
        return $imgUrl;
    }
}
