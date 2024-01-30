<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\User;
use App\Models\UserPayment;
use EdwardMuss\Rave\Facades\Rave as Flutterwave;
use Illuminate\Http\Request;

class FlutterwaveController extends Controller
{
    //

    public function initialize($id)
    {
        //This generates a payment reference
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
                "phone_number" => request()->phone,
                "name" => request()->name
            ],
            'meta' => [
                'user_id' => auth()->user()->id,
                'package_id' => $id,
            ],

            "customizations" => [
                "title" => 'Buy Me Coffee',
                "description" => "Let express love of coffee"
            ]
        ];

        $payment = Flutterwave::initializePayment($data);


        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return;
        }

        return response($payment['data']['link']);
    }

    public function callback()
    {
        $status = request()->status;

        // if payment is successful
        if ($status == 'successful') {
            $transactionID = Flutterwave::getTransactionIDFromCallback();
            $data = Flutterwave::verifyTransaction($transactionID);

            $payment = new Payment();
            $payment->package_id = $data['data']['meta']['package_id'];
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
                $user = User::find($payment->user_id);
                if ($user) {
                    $user->user_status = 1;
                    $user->save();
                }
                return response()->json([
                    'status' => 'success',
                    'message' => 'Payment complete',
                    'data' => $payment,
                ],200);
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



    //payment for user

    public function userPayment($id)
    {
        //This generates a payment reference
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
                "phone_number" => request()->phone,
                "name" => request()->name
            ],
            'meta' => [
                'user_id' => auth()->user()->id,
                'booking_id' => $id,
            ],

            "customizations" => [
                "title" => 'Buy Me Coffee',
                "description" => "Let express love of coffee"
            ]
        ];

        $payment = Flutterwave::initializePayment($data);


        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return;
        }

        return response($payment['data']['link']);
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
                ],200);
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

}
