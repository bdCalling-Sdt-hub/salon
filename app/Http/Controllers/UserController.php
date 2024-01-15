<?php

namespace App\Http\Controllers;

use App\Events\SendNotification;
use App\Models\EmailVerification;
use App\Models\LoginActivity;
use App\Models\PasswordReset;
use App\Models\ServiceRating;
use App\Models\User;
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
            $user->save();
            $this->Otp($user);
//            return response()->json(['success' => true,
//                'user' => $user,
//                'msg' => 'OTP has been sent']);
            return sendNotification('is registered',$user);
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
            return response()->json(['message' => 'Otp is send.']);
        } else {
            return response()->json(['message' => 'You should register first.']);
        }
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'email' => 'required|string|email',
            'password' => 'required|string|min:6'
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(),400);
        }
        $credentials = $request->only('email', 'password');
        if ($token = auth()->attempt($credentials)){


            if(Auth::user()->is_verified==0){
                return response()->json(['error' => 'Your email is not verified'], 401);
            }else{
                return $this->responseWithToken($token);
            }

        }

        return response()->json(['error' => 'Your credential is wrong'], 401);

    }
    protected function responseWithToken($token)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 600
        ]);
    }

    public function verification($id)
    {
        $user = User::where('id', $id)->first();
        $veryStatus = $user->is_verified;
        if ($veryStatus == 1) {
            return response()->json(['message' => 'You are already verified.']);
        } else {
            $email = $user->email;
            return response()->json(['message' => 'Please send OTP to this email :  ' . $email]);
        }
    }

    public function verifiedOtp(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $otpData = EmailVerification::where('otp', $request->otp)->first();
        if (!$otpData) {
            return response()->json(['success' => false, 'msg' => 'You entered wrong OTP']);
        } else {
            $currentTime = time();
            $time = $otpData->created_at;

            if ($currentTime >= $time && $time >= $currentTime - (180 + 5)) {
                User::where('id', $user->id)->update([
                    'is_verified' => 1
                ]);

                $token = auth()->login($user);
                return response()->json([
                    'success' => true,
                    'msg' => 'OTP has been verified',
                    'token' => $this->responseWithToken($token),
                ]);
            } else {
                return response()->json(['success' => false, 'msg' => 'Your OTP has been Expired']);
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
            return response()->json(['success' => false, 'msg' => 'Please try after some time']);
        } else {
            $this->Otp($user);
            return response()->json(['success' => true, 'msg' => 'OTP has been sent']);
        }
    }

    public function resetPassword(request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed'
        ]);
        $user = User::find($request->id);
        $user->password = Hash::make($request->password);
        $user->save();

        PasswordReset::where('email', $user->email)->delete();

        return '<h1> Your password has been reset successfully</h1>';
    }

    public function logout()
    {
        try {
            auth()->logout();
            return response()->json(['status' => true, 'message' => 'User Successfully Logged Out']);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function profile()
    {
        try {
            return response()->json(auth()->user());
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function profileUpdate(Request $request)
    {
        if (auth()->user()) {
            $validator = Validator::make($request->all(), [
                'id' => 'required',
                'name' => 'required|string',
                'email' => 'required|email|string',
                'UserImage' => 'required|image|mimes:jpg,png,jpeg,gif,svg',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $avatar = time() . '.' . $request->UserImage->extension();
            $request->UserImage->move(public_path('images'), $avatar);

            $user = User::find($request->id);
            $user->id = $request->id;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->image = $avatar;
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->save();
            return response()->json(['status' => true, 'message' => 'user is updated', 'Data' => $user]);
        } else {
            return response()->json(['status' => false, 'message' => 'User is not Authenticated']);
        }
    }

    public function refreshToken()
    {
        if (auth()->user()) {
            return $this->responseWithToken(auth()->refresh());
        } else {
            return response()->json(['success' => false, 'message' => 'User is not authenticated.']);
        }
    }

    public function loginActivity($email, $password)
    {
        $admin = User::where('email', $email)->first();
        if ($admin && Hash::check($password, $admin->password)) {
            $agent = new Agent();
            $browser = $agent->browser();
            $device = $agent->device();


            $activity = new LoginActivity([
                'user_id' => $admin->id,
                'browser' => $browser,
                'device_name' => $device,
                'location' => 'Dhaka Bangladesh',
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
        $rating->save();
        return response()->json(['message' => 'Review and ratings are added.']);
    }

    public function editServiceRating($id)
    {
        $editeRating = ServiceRating::where('id', $id)->first();
        return response()->json([
            'status' => 'success',
            'message' => $editeRating
        ]);
    }

    public function updateServiceRating(Request $request, $id)
    {
        $rating = ServiceRating::find($id);
        $rating->review = $request->review;
        $rating->rating = $request->rating;
        $rating->save();
        return response(['status' => '200', 'message' => 'Ratings and reviews are updated successfully']);
    }

    public function deleteServiceRating($id)
    {
        $rating = ServiceRating::find($id);
        $rating->delete();
        return response(['status' => '200', 'message' => 'Ratings and reviews  are deleted successfully']);
    }

    public function showServiceRating()
    {
        $rating = ServiceRating::all();
        return $rating;
    }

//    public function sendNotification(Request $request)
//    {
//        try {
//            event(new SendNotification($request->message, $request->name));
//
//            return response()->json([
//                'success' => true,
//                'msg' => 'Notification Added',
////                'data' => auth()->user()->name,
//            ]);
//        } catch (\Exception $e) {
//            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
//        }
//    }
}
