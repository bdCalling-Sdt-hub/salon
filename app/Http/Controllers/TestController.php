<?php

namespace App\Http\Controllers;

use App\Models\Cat;
use App\Models\Rev;
use App\Models\Sal;
use App\Models\Ser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TestController extends Controller
{
    //

    public function addCat(Request $request){
        $validator = Validator::make($request->all(),[
            'category_name' => 'required|string|min:2|max:15|unique:categories',
            'category_image' => 'required|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),400);
        }
        $category = new Cat();
        $category->category_name = $request->category_name;
        if ($request->file('category_image')){
            $category->category_image = $this->saveImage($request);
        }
        $category->save();
        return ResponseMethod('Category add successfully',$category);
    }

    public function addSal(Request $request){
        $validator = Validator::make($request->all(),[
            'cat_id' => 'required',
            'salon_name' => 'required|string|min:2|max:15|unique:sals',
            'description' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),400);
        }
        $salon = new Sal();
        $salon->cat_id = $request->cat_id;
        $salon->provider_id = auth()->user()->id;
        $salon->salon_name = $request->salon_name;
        $salon->description = $request->description;
        $salon->save();
        return ResponseMethod('Salon add successfully',$salon);
    }

    protected function saveImage($request){
        $image = $request->file('category_image');
        $imageName = rand() . '.' . $image->getClientOriginalExtension();
        $directory = 'TestAsset/category-image/';
        $imgUrl = $directory . $imageName;
        $image->move($directory, $imageName);
        return $imgUrl;
    }

    public function addSer(Request $request){
        $validator = Validator::make($request->all(),[
            'sal_id' => 'required',
            'service_name' => 'required|string|min:2|max:15|unique:sers',
            'service_description' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(),400);
        }
        $service = new Ser();
        $service->sal_id = $request->sal_id;
        $service->service_name = $request->service_name;
        $service->service_description = $request->service_description;
        $service->save();
        return ResponseMethod('Service add successfully',$service);
    }

    public function saveRev(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ser_id' => 'required',
            'rating' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $checkUser = auth()->user();
        $userId = $checkUser->id;

        $rating = new Rev();
        $rating->user_id = $userId;
        $rating->ser_id = $request->ser_id;
        $rating->description = $request->description;
        $rating->rating = $request->rating;
        $rating->save();
        return response()->json(['message' => 'Review and ratings are added.']);
    }

    public function relationFilter(){

        return Sal::with('ser')->get();

//        public function serviceDetails($id)
//        {
//            return Service::where('id', $id)->with('ServiceRating')->get();
//        }

//        public function selonDetails($id)
//        {
//            return Provider::where('id', $id)->with('salonDetails', 'providerRating')->get();
//        }
    }
    public function getReviews(){
        return Ser::with('rev')->get();
//        return Destination::addSelect(['last_flight' => Flight::select('name')
//            ->whereColumn('destination_id', 'destinations.id')
//            ->orderByDesc('arrived_at')
//            ->limit(1)
//        ])->get();
    }
}
