<?php
//
//namespace App\Http\Controllers;
//
//use App\Models\Subscription;
//use EdwardMuss\Rave\Facades\Rave as Flutterwave;
//use Illuminate\Http\Request;
//
//class SubscriptionController extends Controller
//{
//    //
//
//    public function Subscription(){
//        // This generates a payment reference
//        $reference = Flutterwave::generateReference();
//
//        // Enter the details of the payment
//        $data = [
//            'id' => auth()->user()->id,
//            'payment_options' => request()->payment_type,
//            'amount' => request()->amount,
//            'email' => request()->email,
//            'tx_ref' => $reference,
//            'currency' => request()->currency,
//            'redirect_url' => route('callback'),
//            'customer' => [
//                'email' => auth()->user()->email,
//                'name' => auth()->user()->name,
//            ],
//            'customizations' => [
//                'id' => auth()->user()->id,
//                'title' => request()->service_name,
//                'description' => request()->description,
//            ]
//        ];
//
//        $payment = Flutterwave::initializePayment($data);
//
//        if ($payment['status'] !== 'success') {
//
//        }
//        return response($payment['data']['link']);
//    }
//
//    public function callback()
//    {
//        $status = request()->status;
//
//        // if payment is successful
//        if ($status == 'successful') {
//            $transactionID = Flutterwave::getTransactionIDFromCallback();
//            $data = Flutterwave::verifyTransaction($transactionID);
//
//
//            $payment = new Subscription();
//            $payment->user_id = 1;
//            $payment->tx_ref = $data['data']['tx_ref'];
//            $payment->amount = $data['data']['amount'];
//            $payment->currency = $data['data']['currency'];
//            $payment->payment_type = $data['data']['payment_type'];
//            $payment->status = $data['data']['status'];
//            $payment->email = $data['data']['customer']['email'];
//            $payment->name = $data['data']['customer']['name'];
//            $payment->save();
//
//            if ($payment) {
//                return response()->json([
//                    'status' => 'success',
//                    'message' => 'Payment complete',
//                ]);
//            }
//
//        } elseif ($status == 'cancelled') {
//            return response()->json([
//                'status' => 'cancelled',
//                'message' => 'Cancel your payment'
//            ]);
//            // Put desired action/code after transaction has been cancelled here
//        } else {
//            // return getMessage();
//            // Put desired action/code after transaction has failed here
//        }
//    }
//
//}
