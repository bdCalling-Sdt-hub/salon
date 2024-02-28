<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class LoginActivityController extends Controller
{
    //
    public function loginActivity()
    {
        $login_activity = LoginActivity::where('status', 1)->get();
        return ResponseMethod('Login Activity List', $login_activity);
    }

    public function signOutLoginActivity($admin_id)
    {
        $user = LoginActivity::where('id', $admin_id)->where('status', '1')->first();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $user->status = 0;
        $user->update();
        // auth()->logout();
        return response()->json(['message' => 'User logged out successfully']);
    }
}
