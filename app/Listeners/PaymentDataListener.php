<?php

namespace App\Listeners;

use App\Events\PaymentDataEvent;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PaymentDataListener
{
    public function __construct()
    {

    }


    public function handle(PaymentDataEvent $event): void
    {
         $data = $event->data;
         $payment = new Payment();

         $payment->transaction_id = $data['tx_ref'];
         $payment->amount = $data['amount'];
         $payment->email = $data['customer']['email'];
         $payment->name = $data['customer']['name'];
         $payment->phone_number = $data['customer']['phone_number'];
         $payment->save();

    }
}
