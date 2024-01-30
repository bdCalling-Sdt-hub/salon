<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PackageController extends Controller
{
    public function showPackage()
    {
        $packages = Package::get();
        $dataDecode = [];
        foreach ($packages as &$package) {
            $package['package_features'] = json_decode($package['package_features'], true);
            $dataDecode[] = $package;
        }
        return response()->json([
            'message' => 'Package list',
            'data' => $dataDecode
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
            'package_features' => 'string',
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

    public function myPlan()
    {
        $auth_user = auth()->user()->id;

        if (!$auth_user) {
            return response()->json([
                'message' => 'User is not authenticated'
            ], 401);
        }

        $subscription_user = Payment::where('user_id', $auth_user)->with('package')->get();
        return response()->json([
            'message' => 'success',
            'data' => $subscription_user,
        ]);
    }
}
