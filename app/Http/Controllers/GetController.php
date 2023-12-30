<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\User;
use Illuminate\Http\Request;

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
    //search provider request
    public function searchProviderRequest($name=null,$id = null){

        $query = User::where('user_type', 'provider');

        if ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if ($id) {
            $query->Where('id', $id);
        }
        $users = $query->get();

        return ResponseMethod('provider data', $users);
    }

}
