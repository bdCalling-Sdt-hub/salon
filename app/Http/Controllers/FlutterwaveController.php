<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\UserPayment;
use EdwardMuss\Rave\Facades\Rave as Flutterwave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FlutterwaveController extends Controller
{
    //

    public function initialize($id)
    {
        // This generates a payment reference
        $reference = Flutterwave::generateReference();

        // Enter the details of the payment
        $data = [
            'payment_options' => request()->payment_type,
            'amount' => request()->amount,
            'email' => request()->email,
            'tx_ref' => $reference,
            'currency' => request()->currency,
            'redirect_url' => route('callback'),
            'customer' => [
                'email' => request()->email,
                'phone_number' => request()->phone,
                'name' => request()->name
            ],
            'meta' => [
                'user_id' => auth()->user()->id,
                'package_id' => $id,
            ],
            'customizations' => [
                'title' => 'Buy Me Coffee',
                'description' => 'Let express love of coffee'
            ]
        ];

        $payment = Flutterwave::initializePayment($data);

        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return response()->json([
                'message' => 'Something went wrong',
            ],500);
        }

        return response()->json([
            'status' => 'success',
            'gateway_link' => $payment['data']['link'],
        ]);
    }
    public function callback()
    {
        $status = request()->status;

        // if payment is successful
        if ($status == 'successful') {
            $transactionID = Flutterwave::getTransactionIDFromCallback();
            $data = Flutterwave::verifyTransaction($transactionID);

            $auth_user = $data['data']['meta']['user_id'];
            $provider = Payment::where('user_id', $auth_user)->first();

            if (!$provider) {
                $payment = new Payment();
            } else {
                $payment = $provider;
            }

            $payment->package_id = $data['data']['meta']['package_id'];
            $payment->user_id = $auth_user;
            $payment->tx_ref = $data['data']['tx_ref'];
            $payment->amount = $data['data']['amount'];
            $payment->currency = $data['data']['currency'];
            $payment->payment_type = $data['data']['payment_type'];
            $payment->status = $data['data']['status'];
            $payment->email = $data['data']['customer']['email'];
            $payment->name = $data['data']['customer']['name'];
            $payment->save();

            if ($payment) {
                $user = User::find($auth_user);
                if ($user) {
                    $user->user_status = 1;
                    $user->save();
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment complete',
                    'data' => $payment,
                ], 200);
            }

        } elseif ($status == 'cancelled') {
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Cancel your payment'
            ]);
            // Put desired action/code after transaction has been cancelled here
        } else {
            // return getMessage();
            // Put desired action/code after transaction has failed here
        }
    }


    // payment for user

    public function userPayment($id)
    {
        // This generates a payment reference
        $reference = Flutterwave::generateReference();

        // Enter the details of the payment
        $data = [
            'payment_options' => request()->payment_type,
            'amount' => request()->amount,
            'email' => request()->email,
            'tx_ref' => $reference,
            'currency' => request()->currency,
            'redirect_url' => route('user.callback'),
            'customer' => [
                'email' => request()->email,
                'phone_number' => request()->phone,
                'name' => request()->name
            ],
            'meta' => [
                'user_id' => auth()->user()->id,
                'booking_id' => $id,
            ],
            'customizations' => [
                'title' => 'Buy Me Coffee',
                'description' => 'Let express love of coffee'
            ]
        ];

        $payment = Flutterwave::initializePayment($data);

        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return response()->json([
                'message' => 'Something went wrong'
            ],402);
        }
        return response()->json([
            'message' => 'success',
            'link' => $payment['data']['link']
        ]);
    }

    public function userCallback()
    {
        $status = request()->status;

        // if payment is successful
        if ($status == 'successful') {
            $transactionID = Flutterwave::getTransactionIDFromCallback();
            $data = Flutterwave::verifyTransaction($transactionID);

            $payment = new UserPayment();
            $payment->booking_id = $data['data']['meta']['booking_id'];
            $payment->user_id = $data['data']['meta']['user_id'];
            $payment->tx_ref = $data['data']['tx_ref'];
            $payment->amount = $data['data']['amount'];
            $payment->currency = $data['data']['currency'];
            $payment->payment_type = $data['data']['payment_type'];
            $payment->status = $data['data']['status'];
            $payment->email = $data['data']['customer']['email'];
            $payment->name = $data['data']['customer']['name'];
            $payment->save();

            if ($payment) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment complete',
                    'data' => $payment,
                ], 200);
            }
        } elseif ($status == 'cancelled') {
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Cancel your payment'
            ]);
            // Put desired action/code after transaction has been cancelled here
        } else {
            // return getMessage();
            // Put desired action/code after transaction has failed here
        }
    }

    public function paymentSuccess(Request $request){

        $status = $request->status;

        // if payment is successful
        if ($status == 'successful') {

            $auth_user = $request->user_id;
            $provider = Payment::where('user_id', $auth_user)->first();

            if (!$provider) {
                $payment = new Payment();
            } else {
                $payment = $provider;
            }
            $payment->package_id = $request->package_id;
            $payment->user_id = $request->user_id;
            $payment->tx_ref = $request->tx_ref;
            $payment->amount = $request->amount;
            $payment->currency = $request->currency;
            $payment->payment_type = $request->payment_type;
            $payment->status = $request->status;
            $payment->email = $request->email;
            $payment->name = $request->name;
            $payment->save();
            if ($payment) {
                $user = User::find($auth_user);
                if ($user) {
                    $user->user_status = 1;
                    $user->save();
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment complete',
                    'data' => $payment,
                ], 200);
            }

        } elseif ($status == 'cancelled') {
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Your payment is canceled'
            ]);
            // Put desired action/code after transaction has been cancelled here
        } else {
            // return getMessage();
            // Put desired action/code after transaction has failed here
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Your transaction has been failed'
            ]);
        }
    }

    public function UserpaymentSuccess(Request $request){
    return $request;
        $status = $request->status;

        // if payment is successful
        if ($status == 'successful') {

            $auth_user = $request->user_id;
            $provider = UserPayment::where('user_id', $auth_user)->first();

            if (!$provider) {
                $payment = new Payment();
            } else {
                $payment = $provider;
            }
            $payment->booking_id = $request->booking_id;
            $payment->user_id = $request->user_id;
            $payment->tx_ref = $request->tx_ref;
            $payment->amount = $request->amount;
            $payment->currency = $request->currency;
            $payment->payment_type = $request->payment_type;
            $payment->status = $request->status;
            $payment->email = $request->email;
            $payment->name = $request->name;
            $payment->save();
            if ($payment) {
                $user = User::find($auth_user);
                if ($user) {
                    $user->user_status = 1;
                    $user->save();
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment complete',
                    'data' => $payment,
                ], 200);
            }

        } elseif ($status == 'cancelled') {
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Your payment is canceled'
            ]);
            // Put desired action/code after transaction has been cancelled here
        } else {
            // return getMessage();
            // Put desired action/code after transaction has failed here
            return response()->json([
                'status' => 'cancelled',
                'message' => 'Your transaction has been failed'
            ]);
        }
    }
}
