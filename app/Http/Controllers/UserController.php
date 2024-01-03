<?php

namespace App\Http\Controllers;
use App\Events\SendNotification;
use Illuminate\Http\Request;
use App\Models\ServiceRating;
use App\Models\User;
use App\Models\EmailVerification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Validator;
use Illuminate\Support\Facades\Mail;
use App\Models\PasswordReset;
use Laravel\Socialite\Facades\Socialite;
class UserController extends Controller
{
    

    public function register(Request $request)
    {
    $request->validate([
            'name' => 'required|min:2',
            'email' => 'email|required|max:100|unique:users',
            'password' =>'required|confirmed|min:6',
            'UserImage' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048|dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000',
            'phone_number'=> 'required|min:10',
            'address'=>'required|min:2'
        ]);
        $avatar = time() . '.' . $request->UserImage->extension();
        $request->UserImage->move(public_path('images'), $avatar);
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->image = $avatar;
        $user->phone_number= $request->phone_number;
        $user->address = $request->address;
        $user->save();

        return response()->json([$user]);
        
    }

    public function sendOtp(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        if($user){
        $otp = rand(100000,999999);
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

        $data['body'] = 'Your OTP is:- '.$otp;

        Mail::send('mailVerification',['data'=>$data],function($message) use ($data){
            $message->to($data['email'])->subject($data['title']);
        });
        return response()->json(['message'=>'Otp is send.']);
    }
    else{
        return response()->json(['message'=>'You should register first.']);
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
        if (!$token = auth()->attempt($validator->validated()))
        {
            return response() ->json(['error'=>'Unauthorized']);
        }
        return $this->responseWithToken($token);
    }

    protected function responseWithToken($token){
        return response()->json([
            'success'=>true,
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>auth()->factory()-> getTTL() * 60
        ]);

    }
    public function verification($id)
    {
        $user = User::where('id',$id)->first();
        $veryStatus= $user->is_verified;
        if($veryStatus == 1){
            return response()->json(['message'=>'You are already verified.']);
        }
    else{
        $email = $user->email;
    return response()->json(['message'=>'Please send OTP to this email :  '.$email]);
    }
}

    public function verifiedOtp(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        $otpData = EmailVerification::where('otp',$request->otp)->first();
        if(!$otpData){
            return response()->json(['success' => false,'msg'=> 'You entered wrong OTP']);
        }
        else{

            $currentTime = time();
            $time = $otpData->created_at;

            if($currentTime >= $time && $time >= $currentTime - (180+5)){
                User::where('id',$user->id)->update([
                    'is_verified' => 1
                ]);
                return response()->json(['success' => true,'msg'=> 'OTP has been verified']);
            }
            else{
                return response()->json(['success' => false,'msg'=> 'Your OTP has been Expired']);
            }

        }
    }

    public function Otp($user){
       $otp = rand(100000,999999);
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

        $data['body'] = 'Your OTP is:- '.$otp;

        Mail::send('mailVerification',['data'=>$data],function($message) use ($data){
            $message->to($data['email'])->subject($data['title']);
        });
    }

    public function resendOtp(Request $request)
    {
        $user = User::where('email',$request->email)->first();
        $otpData = EmailVerification::where('email',$request->email)->first();

        $currentTime = time();
        $time = $otpData->created_at;

        if($currentTime >= $time && $time >= $currentTime - (180+5)){
            return response()->json(['success' => false,'msg'=> 'Please try after some time']);
        }
        else{
            $this->Otp($user);
            return response()->json(['success' => true,'msg'=> 'OTP has been sent']);
    }
}
    public function resetPassword(request $request){
        $request->validate([
        'password'=>'required|string|min:6|confirmed'
        ]);
        $user= User::find($request->id);
        $user->password=Hash::make($request->password);
        $user->save();

        PasswordReset::where('email',$user->email)->delete();

        return "<h1> Your password has been reset successfully</h1>";

    }
    public function logout()
    {
        try{
        auth()->logout();
        return response()->json(['status'=>true,'message'=>'User Successfully Logged Out']);
    }
    catch(\Exception $e){
        return response()->json(['status'=>false,'message'=>$e->getMessage()]);
    }
}
public function profile()
{
    try{
      
        return response()->json(auth()->user());
    }
    catch(\Exception $e){
        return response()->json(['status'=>false,'message'=>$e->getMessage()]);
    }
}

public function profileUpdate(Request $request){
    if(auth()->user()){
        $validator= Validator::make($request->all(),[
            'id'=>'required',
            'name'=>'required|string',
            'email'=>'required|email|string',
            'UserImage' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048|dimensions:min_width=100,min_height=100,max_width=1000,max_height=1000',
            'phone_number'=> 'required|min:10',
            'address'=>'required|min:2'
            ]);
        if ($validator->fails()){
        return response()->json($validator->errors(),400);
        }
        $avatar = time() . '.' . $request->UserImage->extension();
        $request->UserImage->move(public_path('images'), $avatar);

        $user=User::find($request->id);
        $user->id=$request->id;
        $user->name=$request->name ;
        $user->email=$request->email ;
        $user->image=$avatar;
        $user->phone_number= $request->phone_number;
        $user->address = $request->address;
        $user->save();
            return response()->json(['status'=>true,'message'=>'user is updated','Data'=>$user]);  
                }
            else{
                return response()->json(['status'=>false,'message'=>'User is not Authenticated']);  
            }
}
public function refreshToken()
    {
        if(auth()->user()){
            return $this->responseWithToken(auth()->refresh());
      }
      else{
        return response()->json(['success'=>false,'message'=>'User is not authenticated.']);
      }
    }
    public function deleteUser($id){

        $user=  User::find($id);
        $user ->delete();
           if($user){
                return response()->json([
                    'status'=>True,
                'message'=> "user is deleted"
                ]);
            
        }else
        {
            return response([
                'status'=>false,
                'message'=> "you have no access"
            ]);
            }
    }
    public function loginWithGoogle(){
        return  Socialite::driver('google')->redirect();
     }
     public function callbackFromGoogle(){
        try{
         $user= Socialite::driver('google')->user();
         $is_user = User::where('email',$user->getEmail())->first();
         if(!$is_user){
            $saveUser= User::updateOrCreate(
                 [
                     'google_id'=>$user->getId()
                 ],
                 [
                     'name'=>$user->getName(),
                     'email'=>$user->getEmail(),
                     'password'=>Hash::make($user->getName().'@'.$user->getId())
                 ]
             );
         }else{
             $saveUser= User::where('email',$user->getEmail())->update([
                 'google_id'=>$user->getId(),
             ]);
             $saveUser= User::where('email',$user->getEmail())->first(); 
         }
 
         Auth::loginUsingId($saveUser->id);
         return redirect()->route('home');
        }catch(\Throwable $th){
 
         throw $th;
        }
     }
     public function saveRating(Request $request){
  
        $validator =Validator::make($request->all(),[
        'user_id'=>'required',
        'service_id'=>'required',
        'review'=>'required',
        'rating'=>'required',
        ]);
        if ($validator->fails()){
            return response()->json($validator->errors(),400);
        
        }
        $checkUser=auth()->user();
        $userId= $checkUser->id;
        
        $rating = new ServiceRating();
        $rating->user_id = $userId;
        $rating->service_id = $request->service_id;
        $rating->review=$request->review ;
        $rating->rating=$request->rating ;
        $rating->save();
        return response()->json(['message'=>'Review and ratings are added.']);
        }

        public function editServiceRating($id)
        {
            $editeRating = ServiceRating::where('id',$id)->first();
            return response()->json([
                'status'=>'success',
                'message'=>$editeRating
            ]);

        }

        public function updateServiceRating(Request $request,$id){
            $rating=ServiceRating::find($id);
            $rating->review=$request->review ;
            $rating->rating=$request->rating ;
            $rating->save();
            return response(['status'=>'200','message'=>'Ratings and reviews are updated successfully']);
           } 
        
        
           public function deleteServiceRating($id){
            $rating=ServiceRating::find($id);
            $rating->delete();
            return response(['status'=>'200','message'=>'Ratings and reviews  are deleted successfully']);
           } 
        
        
           
           public function showServiceRating(){
            $rating=ServiceRating::all();
            return $rating;
           } 
           public function sendNotification(Request $request)
    {
        try {

            event(new SendNotification($request->message, auth()->user()->id));

            return response()->json(['success'=>true,'msg'=>'Notification Added']);
        } catch (\Exception $e) {
            return response()->json(['success'=>false,'msg'=>$e->getMessage()]);
        }
    }
}
