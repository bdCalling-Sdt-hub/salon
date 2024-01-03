<?php

namespace App\Http\Controllers;
use App\Http\Requests\PaymentRequest;
use Illuminate\Http\Request;
use EdwardMuss\Rave\Facades\Rave as Flutterwave;

class FlutterwaveController extends Controller
{
    public function initialize(PaymentRequest $request)
    {
        //This generates a payment reference
        $reference = Flutterwave::generateReference();

        $name = $request->name;
        $email = $request->email;
        $phone = $request->phone_number;
        $amount = $request->amount;
        // Enter the details of the payment
        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' =>$amount,
            'email' => $email,
            'tx_ref' => $reference,
            'currency' => "USD",
            'redirect_url' => route('callback'),
            'customer' => [
                'email' => $email,
                "phone_number" => $phone,
                "name" => $name
            ],

            "customizations" => [
                "title" => 'ATYOSE',
                "description" => "Let express love of coffee"
            ]
        ];

     $payment = Flutterwave::initializePayment($data);


        if ($payment['status'] !== 'success') {
            // notify something went wrong
            return response()->json(['status'=>'fail']);
        }

        return $payment;
        
    }

    /**
     * Obtain Rave callback information
     * @return void
     */
    public function callback()
    {
        
        $status = request()->status;

        //if payment is successful
        if ($status ==  'successful') {
        
        $transactionID = Flutterwave::getTransactionIDFromCallback();
        $data = Flutterwave::verifyTransaction($transactionID);

        dd($data);
        }
        elseif ($status ==  'cancelled'){
            //Put desired action/code after transaction has been cancelled here
        }
        else{
            //Put desired action/code after transaction has failed here
        }
}
}
