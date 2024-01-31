<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    public function showPackage()
    {
        $packages = Package::get();
        $package_features = [];
        foreach ($packages as &$package) {
            $package['package_features'] = json_decode($package['package_features']);
            $package_features[] = $package;
        }
        return response()->json([
            'message' => 'Package List',
            'data' => $package_features
        ]);
    }

    public function showSinglePackage($id)
    {
        $Package = Package::where('id', $id)->first();
        if ($Package) {
            return ResponseMethod('Package list', $Package);
        } else {
            return ResponseMessage('Package Not Exist');
        }
    }

    public function addPackage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_name' => 'required|string|min:2|max:15|unique:packages',
            'package_duration' => 'string',
            'package_features' => 'required',
            'price' => 'integer',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
        $package = new Package();
        $package->package_name = $request->package_name;
        $package->package_duration = $request->package_duration;
        $package->package_features = $request->package_features;
        $package->price = $request->price;
        $package->save();
        return ResponseMethod('Package add successfully', $package);
    }

    public function updatePackage(Request $request, $id)
    {
        $package = Package::where('id', $id)->first();
        if ($package) {
            $validator = Validator::make($request->all(), [
                'package_name' => 'string|min:2|max:15',
                'package_duration' => 'string',
                'package_features' => 'string',
                'price' => 'integer',
            ]);
            if ($validator->fails()) {
                return response()->json($validator->errors(), 400);
            }
            $package->package_name = $request->package_name;
            $package->package_duration = $request->package_duration;
            $package->package_features = $request->package_features;
            $package->price = $request->price;
            $package->update();
            return responseMethod('package update successfully', $package);
        } else {
            return responseMessage('package Not found');
        }
    }

    public function deletePackage($id)
    {
        $package = Package::where('id', $id)->first();
        if ($package) {
            $package->delete();
            return responseMessage('Package delete successfully');
        }
        return responseMessage('Package Not Found');
    }

    public function packageRenew($id){
        return $user = User::find($id);
//        $package_id = $id;
//        $check_user = auth()->user()->id;
//        $subscription = Payment::where('user_id',$check_user)->with('package')->first();
//        $start_date = $subscription['package']['created_at'];
//        $package_duration = $subscription['package']['package_duration'];
//        if ($subscription){
//            return response()->json([
//                'message' => 'You already have subscription package',
//                'data' => $subscription,
//                'created_at' => $start_date,
//                'package_duration' =>
//            ]);
//        }
//        return $subscription;
        $package = Package::find($id);
        if(!$package){
            return response()->json([
                'message' => 'Package does not exist',
                'data' => []
            ]);
        }
        $subscription = new Subscription();
        $subscription->user_id = auth()->user()->id;
        $subscription->package_id = $package->id;
        $subscription->package_name = $package->package_name;
        $subscription->package_duration = $package->package_duration;
        $subscription->package_features = $package->package_features;
        $subscription->price = $package->price;
        $subscription->save();
        return ResponseMethod('Subscription add successfully', $subscription);


    }

    public function myPlan()
    {
        $auth_user = auth()->user()->id;

        if (!$auth_user) {
            return response()->json([
                'message' => 'User is not authenticated'
            ], 401);
        }

        $subscriptions = Payment::where('user_id', $auth_user)->with('package')->get();
        if(!$subscriptions){
            return response()->json([
                'message' => 'existing plan not found',
                'data' => []
            ],404);
        }
        $result = [];
        foreach ($subscriptions as $subscription) {
            // Check if the 'package_features' key exists before decoding
            $packageFeatures = isset($subscription->package->package_features)
                ? json_decode($subscription->package->package_features, true)
                : null;

            $result[] = [
                'user_id' => $subscription->user_id,
                'package_id' => $subscription->package_id,
                'payment_type' => $subscription->payment_type,
                'amount' => $subscription->amount,
                'email' => $subscription->email,
                'name' => $subscription->name,
                'currency' => $subscription->currency,
                'tx_ref' => $subscription->tx_ref,
                'status' => $subscription->status,
                'created_at' => $subscription->created_at,
                'updated_at' => $subscription->updated_at,
                'package' => [
                    'package_id' => $subscription->package->id,
                    'package_name' => $subscription->package->package_name,
                    'package_duration' => $subscription->package->package_duration,
                    'package_features' => $packageFeatures,
                    'price' => $subscription->package->price,
                    'created_at' => $subscription->package->created_at,
                    'updated_at' => $subscription->package->updated_at,
                ],
            ];
        }

        return response()->json([
            'message' => 'success',
            'data' => $result,
        ]);
    }
}
