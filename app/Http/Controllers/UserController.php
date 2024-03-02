<?php

namespace App\Http\Controllers;

use App\Events\SendNotification;
use App\Models\EmailVerification;
use App\Models\LoginActivity;
use App\Models\PasswordReset;
use App\Models\ServiceRating;
use App\Models\User;
use App\Notifications\AdminNotification;
use App\Notifications\SalonNotification;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Jenssegers\Agent\Agent;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $user = User::where('email', $request->email)
            ->where('is_verified', 0)
            ->first();

        if ($user) {
            $otp = $this->Otp($user);
            return response(['message' => 'Please check your email for get otp.', 'exists' => true], 200);
        } else {
            Validator::extend('contains_dot', function ($attribute, $value, $parameters, $validator) {
                return strpos($value, '.') !== false;
            });

            $avatar = 'dummyImg/default.jpg';
            //  $avatar->move(public_path('images'), $avatar);

            $request->validate([
                'name' => 'required|min:2',
                'email' => 'email|required|max:100|unique:users',
                'password' => 'required|confirmed|min:6',
                'user_type' => 'string',
            ]);

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->user_type = $request->user_type;
            $user->image = $avatar;
            $user->save();
            $this->Otp($user);
            return response()->json(['success' => true,
                'user' => $user,
                'msg' => 'OTP has been sent'], 200);
        }
    }

    public function sendOtp(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if ($user) {
            $otp = rand(100000, 999999);
            $time = time();

            EmailVerification::updateOrCreate(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'otp' => $otp,
                    'created_at' => $time
                ]
            );

            $data['email'] = $request->email;
            $data['title'] = 'Mail Verification';

            $data['body'] = 'Your OTP is:- ' . $otp;

            Mail::send('mailVerification', ['data' => $data], function ($message) use ($data) {
                $message->to($data['email'])->subject($data['title']);
            });
            return response()->json(['message' => 'Otp is send.'], 200);
        } else {
            return response()->json(['message' => 'You should register first.'], 401);
        }
    }

    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:2',
            'email' => 'email|required|max:100',
            'user_type' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        // Check if a user with this email exists without Google or Facebook ID
        $manual_user = User::where('email', $request->email)
            ->whereNull('google_id')
            ->whereNull('facebook_id')
            ->first();

        if ($manual_user) {
            return response()->json([
                'message' => 'User already exists. Sign in manually.',
            ], 422);
        } else {
            // Check if a user with this email exists with Google or Facebook ID
            $user = User::where('email', $request->email)
                ->where(function ($query) {
                    $query
                        ->whereNotNull('google_id')
                        ->orWhereNotNull('facebook_id');
                })
                ->first();

            if ($user) {
                if ($token = auth()->login($user)) {
                    return $this->responseWithToken($token);
                }
                return response()->json([
                    'message' => 'User unauthorized'
                ], 401);
            } else {
                $avatar = 'dummyImg/default.jpg';
                // Create a new user
                $user = new User();
                $user->name = $request->name;
                $user->email = $request->email;
                $user->user_type = $request->user_type;
                $user->google_id = $request->google_id ?? null;
                $user->facebook_id = $request->facebook_id ?? null;
                $user->latitude = $request->latitude ?? null;
                $user->longitude = $request->latitude ?? null;
                $user->is_verified = 1;
                $user->image = $avatar;
                $user->save();
                // Generate token for the new user
                if ($token = auth()->login($user)) {
                    return $this->responseWithToken($token);
                }
                return response()->json([
                    'message' => 'User unauthorized'
                ], 401);
            }
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'No user found with the provided email'], 402);
        }
        $credentials = $request->only('email', 'password');

        if ($token = auth()->attempt($credentials)) {
            $user = Auth::user();
            if ($user->is_verified == 0) {
                // User email is not verified
                return response()->json(['message' => 'Your email is not verified'], 402);
            } else {
                // Successful login
                if ($user->user_type == 'admin') {
                    $this->loginActivityAdmin($request->email, $request->password);
                }
                return $this->responseWithToken($token);
            }
        }
        // Incorrect email or password
        return response()->json(['message' => 'Incorrect email or password'], 402);
    }

    protected function responseWithToken($token)
    {
        return response()->json([
            'status' => true,
            'access_token' => $token,
            'user_id' => auth()->user()->id,
            'user_type' => auth()->user()->user_type,
            'google_id' => auth()->user()->google_id,
            'facebook_id' => auth()->user()->facebook_id,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 6000000000000000
        ], 200);
    }

    public function verification($id)
    {
        $user = User::where('id', $id)->first();
        $veryStatus = $user->is_verified;
        if ($veryStatus == 1) {
            return response()->json(['message' => 'You are already verified.'], 409);
        } else {
            $email = $user->email;
            return response()->json(['message' => 'Please send OTP to this email :  ' . $email], 200);
        }
    }

    public function verifiedOtp(Request $request)
    {
        $user = User::where(['email' => $request->email])->first();

        $otpData = EmailVerification::where('otp', $request->otp)->first();
        if (!$otpData) {
            return response()->json(['success' => false, 'msg' => 'You entered wrong OTP'], 401);
        } else {
            $currentTime = time();
            $time = $otpData->created_at;

            if ($currentTime >= $time && $time >= $currentTime - (180 + 5)) {
                User::where('id', $user->id)->update([
                    'is_verified' => 1,
                ]);

                if ($user->user_type === 'user') {
                    $notification = sendNotification('Account Setup Successfull', 'You have successfully created your account', $user);
                } elseif ($user->user_type === 'provider') {
                    $notification = providerNotification('Account Setup Successfull', 'You have successfully created your account', $user);
                } elseif ($user->user_type === 'admin') {
                    $notification = adminNotification('Account Setup Successfull', 'You have successfully created your account', $user);
                }

                $token = auth()->login($user);
                return response()->json([
                    'status' => 'success',
                    'Notification' => $notification,
                    'token' => $this->responseWithToken($token),
                    'Otp' => 'OTP has been verified',
                ], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Your OTP has been Expired'], 410);
            }
        }
    }

    public function Otp($user)
    {
        $otp = rand(100000, 999999);
        $time = time();

        EmailVerification::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'otp' => $otp,
                'created_at' => $time
            ]
        );

        $data['email'] = $user->email;
        $data['title'] = 'Mail Verification';

        $data['body'] = 'Your OTP is:- ' . $otp;

        Mail::send('mailVerification', ['data' => $data], function ($message) use ($data) {
            $message->to($data['email'])->subject($data['title']);
        });
    }

    public function resendOtp(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $otpData = EmailVerification::where('email', $request->email)->first();

        $currentTime = time();
        $time = $otpData->created_at;

        if ($currentTime >= $time && $time >= $currentTime - (180 + 5)) {
            return response()->json(['success' => false, 'msg' => 'Please try after some time'], 429);
        } else {
            $this->Otp($user);
            return response()->json(['success' => true, 'msg' => 'OTP has been sent'], 200);
        }
    }

    public function resetPassword(request $request)
    {
        $check_user = auth()->user()->id;
        $request->validate([
            'password' => 'required|string|min:6|confirmed'
        ]);
        $user = User::find($check_user);
        $user->password = Hash::make($request->password);
        $user->save();

        //         PasswordReset::where('email', $user->email)->delete();

        return response()->json(['Your password changes'], 200);
    }

    public function changePassword(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'current_password' => 'required|string|min:6',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (Hash::check($request->current_password, $user->password)) {
            // Current password matches the user's actual password
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'message' => 'Password changed successfully'
            ], 200);
        }

        // Current password does not match
        return response()->json([
            'message' => 'Current password is not correct'
        ], 404);
    }

    public function logout()
    {
        try {
            auth()->logout();
            return response()->json([
                'status' => true,
                'message' => 'User Successfully Logged Out'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 401);
        }
    }

    public function profile()
    {
        try {
            return response()->json(auth()->user());
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()], 401);
        }
    }

    public function profileUpdate(Request $request)
    {
        if (auth()->user()) {
            $check_user = auth()->user()->id;
            $validator = Validator::make($request->all(), [
                'name' => 'required|string',
                'email' => 'required|email|string',
                'UserImage' => 'image|mimes:jpg,png,jpeg,gif,svg',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user = User::find($check_user);

            if (!$user) {
                return response()->json(['status' => false, 'message' => 'User not found']);
            }

            $user->name = $request->name;
            $user->email = $request->email;
            $user->address = $request->address;

            if ($request->phone_number == '') {
                $request->phone_number = $user->phone_number;
            }

            $user->phone_number = $request->phone_number;

            if ($request->file('image')) {
                // Check if the old image file exists before attempting to unlink it
                if (file_exists($user->image)) {
                    //                    unlink($user->image);
                }
                $user->image = $this->saveImage($request);
            }

            $user->update();
            return response()->json(['status' => true, 'message' => 'User is updated', 'Data' => $user]);
        } else {
            return response()->json(['status' => false, 'message' => 'User is not authenticated']);
        }
    }

    protected function saveImage($request)
    {
        $image = $request->file('image');
        $imageName = rand() . '.' . $image->getClientOriginalExtension();
        $directory = 'Asset/user-image/';
        $imgUrl = $directory . $imageName;
        $image->move($directory, $imageName);
        return $imgUrl;
    }

    public function refreshToken()
    {
        if (auth()->user()) {
            return $this->responseWithToken(auth()->refresh());
        } else {
            return response()->json(['success' => false, 'message' => 'User is not authenticated.'], 200);
        }
    }

    public function loginActivityAdmin($email, $password)
    {
        $admin = User::where('email', $email)->first();
        if ($admin && Hash::check($password, $admin->password)) {
            $agent = new Agent();
            $browser = $agent->browser();
            $device = $agent->device();
            $location = 'Dhaka,Bangladesh';

            $activity = new LoginActivity([
                'user_id' => $admin->id,
                'browser' => $browser,
                'device_name' => $device,
                'location' => $location,
                'login_time' => now(),
                'status' => ($admin && Hash::check($password, $admin->password)) ? 1 : 0,
            ]);
            $activity->save();
        }
    }

    //

    public function saveRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'service_id' => 'required',
            'review' => 'required',
            'rating' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $checkUser = auth()->user();
        $userId = $checkUser->id;

        $rating = new ServiceRating();
        $rating->user_id = $userId;
        $rating->service_id = $request->service_id;
        $rating->review = $request->review;
        $rating->rating = $request->rating;
        $rating->provider_id = $request->providerId;
        $rating->save();
        return response()->json(['message' => 'Review and ratings are added.'], 200);
    }

    public function editServiceRating($id)
    {
        $editeRating = ServiceRating::where('id', $id)->first();
        return response()->json([
            'status' => 'success',
            'message' => $editeRating
        ], 200);
    }

    public function updateServiceRating(Request $request, $id)
    {
        $rating = ServiceRating::find($id);
        $rating->review = $request->review;
        $rating->rating = $request->rating;
        $rating->save();
        return response(['status' => 'success', 'message' => 'Ratings and reviews are updated successfully'], 200);
    }

    public function deleteServiceRating($id)
    {
        $rating = ServiceRating::find($id);
        $rating->delete();
        return response(['status' => 'success', 'message' => 'Ratings and reviews  are deleted successfully'], 200);
    }

    public function showServiceRating()
    {
        $rating = ServiceRating::all();
        return $rating;
    }

    //    // login activity
    //
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
