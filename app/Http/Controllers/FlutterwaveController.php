<?php

namespace App\Http\Controllers;

use App\Events\PaymentDataEvent;
use EdwardMuss\Rave\Facades\Rave as Flutterwave;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

class FlutterwaveController extends Controller
{
    //

    public function initialize(Request $request)
    {
        //This generates a payment reference
        $reference = Flutterwave::generateReference();

        // Enter the details of the payment
        $data = [
            'payment_options' => $request->payment_type,
            'amount' => $request->payment,
            'email' => $request->email,
            'tx_ref' => $reference,
            'currency' => "KES",
            'redirect_url' => route('payment.callback'),
            'customer' => [
                'email' => request()->email,
                "phone_number" => request()->phone,
                "name" => request()->name
            ],

            "customizations" => [
                "title" => 'Buy Me Coffee',
                "description" => "Let express love of coffee",
            ]
        ];

        $payment = Flutterwave::initializePayment($data);

        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return response()->json('payment status is not success');
        }
        return ($payment['data']['link']);
    }

    public function callback()
    {

        $status = request()->status;

        //if payment is successful
        if ($status ==  'successful') {

            $transactionID = Flutterwave::getTransactionIDFromCallback();
            $data = Flutterwave::verifyTransaction($transactionID);

            dd($data);

//            event(new PaymentDataEvent($data));
        }
        elseif ($status ==  'cancelled'){
            //Put desired action/code after transaction has been cancelled here
        }
        else{
            //Put desired action/code after transaction has failed here
        }
        // Get the transaction from your DB using the transaction reference (txref)
        // Check if you have previously given value for the transaction. If you have, redirect to your successpage else, continue
        // Confirm that the currency on your db transaction is equal to the returned currency
        // Confirm that the db transaction amount is equal to the returned amount
        // Update the db transaction record (including parameters that didn't exist before the transaction is completed. for audit purpose)
        // Give value for the transaction
        // Update the transaction to note that you have given value for the transaction
        // You can also redirect to your success page from here
    }
}
