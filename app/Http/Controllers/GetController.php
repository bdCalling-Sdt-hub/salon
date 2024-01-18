<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\ServiceRating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
    public function searchProviderBlock($name=null){
        if (!is_null($name)){
            $block_provider = User::where('user_type', 'provider');
            if (!is_null($block_provider)){
                $block_provider->where('name', 'like', '%' . $name . '%');
                return ResponseMethod('Block Provider data', $block_provider);
            }
            return ResponseMessage('block provider not found');
        }
        return ResponseMessage('write name which one you want to find');
    }

    //search provider
    public function searchProvider($name){
        if (!is_null($name)){

        }
        $query = Provider::where('user_type', 'provider');
        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        $users = $query->get();
        return ResponseMethod('provider data', $users);
    }

    //search user
    public function searchUser($name=null){
        if (!is_null($name)){
            $user = User::where('user_type', 'user')->where('name', 'like', '%' . $name . '%')->get();

            if ($user->count() > 0){
                return ResponseMethod('user data', $user);
            }
            return ResponseMessage('user not found');
        }
        return ResponseMessage('provide name for search');
    }

    //search salon

    public function searchSalon($name=null){

        if (!is_null($name)) {
            $users = Provider::where('business_name', 'like', '%' . $name . '%')->get();

            if ($users->count() > 0) {
                return ResponseMethod('salon data', $users);
            }
            return ResponseMessage('salon not found');
        }
        return ResponseMessage('provide name for searching');
    }



    public function getAppointmentList(){
        $booking = Booking::select('bookings.*', 'users.name as client_name','providers.business_name as name')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->join('providers','bookings.provider_id', '=', 'providers.id')
            ->paginate(12);
        if ($booking){
            return response()->json([
                'status'=>200,
                'message' => 'booking list',
                'data' => $booking,
            ]);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'Data Not found',
                'data' => 'Not found',
            ]);
        }
    }

    public function appointmentListbyId($id){

        $booking = Booking::select('bookings.*', 'users.name as client_name','providers.business_name as name')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->join('providers','bookings.provider_id', '=', 'providers.id')
            ->where('bookings.id', '=', $id)
            ->first();
        if ($booking){
            return response()->json([
                'status'=>200,
                'message' => 'booking list',
                'data' => $booking,
            ]);
        }else{
            return response()->json([
                'status' => 404,
                'message' => 'Data Not found',
                'data' => 'Not found',
            ]);
        }
    }

    public function getReviews(){
//        $review = ServiceRating::select('service_ratings.*','users.name as provider_name')
//            ->join('users','service_ratings.user_id','=','users.id')
//            ->join('services','service_ratings.service_id','=','services.id')
//            ->join('providers','services.provider_id', '=', 'providers.id')
//            ->join('users','providers.provider_id', '=' , 'users.id')
//            ->get();
//        return $review;
        $reviews = ServiceRating::select('service_ratings.*', 'clients.name as client_name', 'provider.name as provider_name')
            ->join('services', 'service_ratings.service_id', '=', 'services.id')
            ->join('providers', 'services.provider_id', '=', 'providers.id')
            ->join('users as clients', 'service_ratings.user_id', '=', 'clients.id') // Join for client name
            ->join('users as provider', 'providers.provider_id', '=', 'provider.id') // Join for provider name
            ->get();
        return $reviews;
    }

    public function getReviewsByProviderId($providerId){
        $reviews = ServiceRating::select('service_ratings.*', 'clients.name as client_name', 'provider.name as provider_name')
            ->join('services', 'service_ratings.service_id', '=', 'services.id')
            ->join('providers', 'services.provider_id', '=', 'providers.id')
            ->join('users as clients', 'service_ratings.user_id', '=', 'clients.id') // Join for client name
            ->join('users as provider', 'providers.provider_id', '=', 'provider.id') // Join for provider name
            ->where('provider.id', '=', $providerId) // Filter by provider ID
            ->get();
        return $reviews;
    }

    //review average rating

    public function averageReviewRating($providerId){
        $reviews = ServiceRating::select('service_ratings.*', 'clients.name as client_name', 'provider.name as provider_name')
            ->join('services', 'service_ratings.service_id', '=', 'services.id')
            ->join('providers', 'services.provider_id', '=', 'providers.id')
            ->join('users as clients', 'service_ratings.user_id', '=', 'clients.id') // Join for client name
            ->join('users as provider', 'providers.provider_id', '=', 'provider.id') // Join for provider name
            ->where('provider.id', '=', $providerId) // Filter by provider ID
            ->get();
        return $reviews;
    }
            public function test($latitude,$longitude){

                $salon = Provider::select(DB::raw("*, ( 6371 * acos( cos( radians('$latitude') )
            * cos( radians( latitude ) )
            * cos( radians( longitude ) - radians('$longitude') )
            + sin( radians('$latitude') )
            * sin( radians( latitude ) ) ) ) AS distance"))->havingRaw('distance < 300')->orderBy('distance')
                    ->get();
                return ResponseMethod('Nearest Salon Data',$salon);
            }

    public function bookingHistory(){
        $booking_history = Payment::all();
        if ($booking_history){
            return response()->json([
                'status' => 'success',
                'data' => $booking_history,
            ]);
        }
        else{
            return response()->json([
                'status' => 'false',
                'message' => 'Booking history is empty'
            ]);
        }
    }

    public function filter($category_name = null){
            if (!is_null($category_name)) {
                $salon = Provider::select('providers.*')
                    ->Join('categories', 'providers.category_id', '=', 'categories.id')
                    ->where('category_name', 'like', '%' . $category_name . '%')
                    ->get();
                if ($salon->count() > 0) {
                    return ResponseMethod('Salon data', $salon);
                }

                return ResponseMessage('Salon not found');
            }

            return ResponseMessage('Provide category name for search');
        }

        public function paymentHistory(){
            $payment_history = Payment::paginate(9);
            if($payment_history){
                return response()->json([
                    'status' => 200,
                    'data' => $payment_history,
                ]);
            }
            return response()->json([
                'status' => 404,
                'message' => 'No data found'
            ]);
        }

    public function paymentHistoryById($id){
        $payment_history = Payment::where('id',$id)->first();
        if($payment_history){
            return response()->json([
                'status' => 200,
                'data' => $payment_history,
            ]);
        }
        return response()->json([
            'status' => 404,
            'message' => 'No data found'
        ]);
    }
}
