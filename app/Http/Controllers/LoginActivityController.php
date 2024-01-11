<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\Request;

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
}
