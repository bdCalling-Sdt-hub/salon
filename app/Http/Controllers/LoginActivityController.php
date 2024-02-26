<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\Request;
//use App\Models\LoginActivity;
use Jenssegers\Agent\Agent;

class LoginActivityController extends Controller
{
    //
    public function loginActivity(){
        $login_activity = LoginActivity::all();
        return ResponseMethod('Login Activity List',$login_activity);
    }

    public function signOutLoginActivity($admin_id){

        $user = User::where('id',$admin_id)->first();
        return $user;

//        if (!$user) {
//            return response()->json(['error' => 'User not found'], 404);
//        }
//
//        auth()->logout();
//        return response()->json(['message' => 'User logged out successfully']);
    }



//$this->loginActivity($request->email,$request->password);

//    public function loginActivityAdmin($email,$password)
//    {
//        $admin = User::where('email', $email)->first();
//        if ($admin && Hash::check($password, $admin->password)) {
//            $agent = new Agent();
//            $browser = $agent->browser();
//            $device = $agent->device();
//
//            $activity = new LoginActivity([
//                'user_id' => $admin->id,
//                'browser' => $browser,
//                'device_name' => $device,
//                'location' => 'Dhaka Bangladesh',
//                'login_time' => now(),
//                'status' => ($admin && Hash::check($password, $admin->password)) ? 1 : 0,
//            ]);
//            $activity->save();
//        }
//    }
}
