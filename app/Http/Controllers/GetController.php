<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Provider;
use App\Models\ServiceRating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GetController extends Controller
{
    //salon
    public function  salonList(Request $request){
        $perPage = $request->input('per_page',7);
        $providers = Provider::paginate($perPage);
        if ($providers){
            return ResponseMethod('Data',$providers);
        }else{
            return ResponseMessage('Provider is empty');
        }
    }
    public function singleSalon($id){
        $singleSalon = Provider::where('id',$id)->first();
        if ($singleSalon){
            return ResponseMethod('data',$singleSalon);
        }else{
            return ResponseMessage('Salon does not exist');
        }
    }

    //salon owner
    public function getProviderRequest(){

        $providerRequest = User::where('user_type','provider')->where('user_status',0)->paginate(12);
        if ($providerRequest){
            return ResponseMethod('Provider Request',$providerRequest);
        }else{
            return ResponseMessage('No Provider Request Found');
        }
    }
    public function approveProviderRequest($id){

            $provider =User::where('user_type','provider')->where('user_status',0)->where('id',$id)->first();
            if ($provider){
                $provider->user_status = 1;
                $provider->update();
                return ResponseMethod('approve provider request',$provider);
            }
            return ResponseMessage('Provider Request is not pending');
    }

    public function blockProviderRequest($id){
        $providerRequest =User::where('user_type','provider')->where('user_status',0)->where('id',$id)->first();
        if ($providerRequest){
            $providerRequest->user_status = 2;
            $providerRequest->update();
            return ResponseMethod('Cancel Provider Successfully',$providerRequest);
        }
        return ResponseMessage('Provider Request does not exist');
    }


    //unblock
    public function providerBlockList(){
        $providerBlockList = User::where('user_type','provider')->where('user_status',2)->select(['image','name','email'])->paginate(12);
        if ($providerBlockList){
            return ResponseMethod('Provider block list',$providerBlockList);
        }
        return ResponseMessage('Block User list is empty');
    }
    public function unblockProvider($id){
        $providerRequest =User::where('user_type','provider')->where('user_status',2)->where('id',$id)->first();
        if ($providerRequest){
            $providerRequest->user_status = 2;
            $providerRequest->update();
            return ResponseMethod('Cancel Provider Successfully',$providerRequest);
        }
        return ResponseMessage('Provider Request does not exist');
    }

    //user list
    public function userList(){
        $user = User::where('user_type','user')->select(['name','email','phone_number','created_at'])->paginate(9);
        if ($user){
            return ResponseMethod('User list',$user);
        }
        return ResponseMessage('User is empty');
    }

    // single user details

    public function singleUser($id){
        $user = User::where('user_type','user')->where('id',$id)->first();
        if ($user){
            return ResponseMethod('User Data',$user);
        }
        return ResponseMessage('User is empty');
    }

    //block user
    public function deleteUser($id){

        $user=  User::find($id);
        $user ->delete();
        if(!is_null($user)){
            return ResponseMessage('User deleted Successfully');
        }
        return ResponseMessage('User does not exist');
    }

    //search provider request
    public function searchProviderRequest($name){
        $query = User::where('user_type', 'provider')->where('user_status',0);

        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        $users = $query->get();
        return ResponseMethod('provider Request list', $users);
    }

    //search provider block
    public function searchProviderBlock($name){

        $query = User::where('user_type', 'provider');
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        $users = $query->get();
        return ResponseMethod('block provider data', $users);
    }

    //search provider
    public function searchProvider($name){
        $query = Provider::where('user_type', 'provider');
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        $users = $query->get();
        return ResponseMethod('provider data', $users);
    }

    //search user
    public function searchUser($name){
        $query = Provider::where('user_type', 'user');
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        $users = $query->get();
        return ResponseMethod('User data', $users);
    }

    //search salon

    public function searchSalon(Request $request, $name=null){

        $validator = Validator::make($request->all(), [
            'business_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if (!is_null($name)) {
            $users = Provider::where('business_name', 'like', '%' . $name . '%')->get();

            if ($users->isEmpty()) {
                return ResponseMessage('salon not found');
            }

            return ResponseMethod('salon data', $users);
        }

        return ResponseMessage('salon not found');
    }



    public function getAppointmentList(){
        $booking = Booking::select('bookings.*', 'users.name as client_name','providers.business_name as name')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->join('providers','bookings.provider_id', '=', 'providers.id')
            ->first();
        return $booking;
    }

    public function getReview(){
        $review = ServiceRating::select('service_ratings.*','users.name as provider_name')
            ->join('users','service_ratings.user_id','=','users.id')
            ->join('services','service_ratings.service_id','=','services.id')
            ->join('providers','services.provider_id', '=', 'providers.id')
            ->join('users','providers.provider_id', '=' , 'users.id')
            ->get();
        return $review;
    }
}
