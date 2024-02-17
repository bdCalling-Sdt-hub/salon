<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class DeleteAccountController extends Controller
{
    //
    public function deleteUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Retrieve user by email
        $user = User::where("email", $request->email)->first();

        // Check if user exists and password is correct
        if ($user && Hash::check($request->password, $user->password)) {
            // Delete the user
            $user->delete();
            return response()->json(['message' => 'User deleted successfully'], 200);
        } else {
            // Incorrect credentials
            return response()->json(['message' => 'Invalid email or password'], 401);
        }
    }

}
