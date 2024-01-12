<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use EdwardMuss\Rave\Facades\Rave as Flutterwave;
use Illuminate\Http\Request;

class PymentController extends Controller
{
    /**
     * Initialize Rave payment process
     * @return void
     */
    public function initialize()
    {
        // This generates a payment reference
        $reference = Flutterwave::generateReference();

        // Enter the details of the payment
        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' => request()->amount,
            'email' => request()->email,
            'tx_ref' => $reference,
            'currency' => request()->currency,
            'redirect_url' => route('callback'),
            'customer' => [
                'email' => request()->email,
                'phone_number' => request()->phone,
                'name' => request()->name,
            ],
            'customizations' => [
                'title' => request()->title,
                'description' => request()->description,
            ]
        ];

        return $payment = Flutterwave::initializePayment($data);

        if ($payment['status'] !== 'success') {
            return response()->json([
                'status' => 'false',
                'message' => 'Something want wrong'
            ]);
        }

        return response($payment['data']['link']);
    }

    /**
     * Obtain Rave callback information
     * @return void
     */
    public function callback()
    {
        $status = request()->status;

        // if payment is successful
        if ($status == 'successful') {
            $transactionID = Flutterwave::getTransactionIDFromCallback();
            $data = Flutterwave::verifyTransaction($transactionID);
            $data['info'] = [
                'descriptin' => 'something',
                'service_id' => 1
            ];
            // dd($data);
            // $authUser = auth()->user()->id;
            $payment = new Payment();
            $payment->tx_ref = $data['data']['tx_ref'];
            $payment->amount = $data['data']['amount'];
            $payment->currency = $data['data']['currency'];
            $payment->pyment_type = $data['data']['payment_type'];
            $payment->status = $data['data']['status'];
            $payment->user_id = 1;
            $payment->save();
            if ($payment) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Pyment complete',
                ]);
            }
            // $payment->amount = $data['amount'];
            // $payment->email = $data['customer']['email'];
            // $payment->name = $data['customer']['name'];
            // $payment->phone_number = $data['customer']['phone_number'];
            // $payment->description = $data['info']['description'];
            // $payment->service_id = $data['info']['service_id'];
            // $payment->save();
            // if ($payment) {
            //     return response()->json([
            //         'status' => 'success',
            //         'message' => 'Payment successfully'
            //     ]);
            // }
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

    // ===================EARNING SECTION=================== //

    public function MonthlyIncome()
    {
        $monthIncom = Payment::selectRow('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', date('Y'))
            ->groupeBy('month')
            ->orderBy('month')
            ->get();

        return $monthIncom;
    }
}
