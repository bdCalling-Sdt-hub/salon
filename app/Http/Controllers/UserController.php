<?php

namespace App\Http\Controllers;

use App\Events\SendNotification;
use App\Models\EmailVerification;
use App\Models\LoginActivity;
use App\Models\PasswordReset;
use App\Models\ServiceRating;
use App\Models\User;
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
//            return response()->json(['success' => true,
//                'user' => $user,
//                'msg' => 'OTP has been sent']);
//             return sendNotification('is registered',$user,);

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


        if (!$token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->json(['error' => 'Your credential is wrong'], 401);

    }
    protected function responseWithToken($token)
    {
        return response()->json([
            'status' => true,
            'access_token' => $token,
            'user_type' => auth()->user()->user_type,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 600000
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

                $token = auth()->login($user);
                return response()->json([
                    'status' => 'success',
                    'Notification' => sendNotification('register complete', $user),
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

                'id' => 'required',
                'name' => 'required|string',
                'email' => 'required|email|string',
                'UserImage' => 'image|mimes:jpg,png,jpeg,gif,svg',

            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }

            $user = User::find($check_user);
            $user->id = $check_user;
            $user->name = $request->name;
            $user->email = $request->email;
            $user->address = $request->address;
            $user->phone_number = $request->phone_number;


            if ($request->file('image')) {
                unlink($user->image);
                $user->image = $this->saveImage($request);
            }
            $user->update();
            return response()->json(['status' => true, 'message' => 'user is updated', 'Data' => $user]);
        } else {
            return response()->json(['status' => false, 'message' => 'User is not Authenticated']);
        }
    }
    protected function saveImage($request){
        $image = $request->file('image');
        $imageName = rand() . '.' . $image->getClientOriginalExtension();
        $directory = 'Asset/user-image/';
        $imgUrl = $directory . $imageName;
        $image->move($directory, $imageName);
        return $imgUrl;
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



    public function sendNotification(Request $request)
    {
        try {
            event(new SendNotification($request->message, $request->name));

            return response()->json([
                'success' => true,
                'msg' => 'Notification Added',
//                'data' => auth()->user()->name,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }
}
