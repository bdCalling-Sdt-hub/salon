<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use EdwardMuss\Rave\Facades\Rave as Flutterwave;
use Illuminate\Http\Request;
use Carbon;
use DB;

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

    public function earningStory()
    {
        // IN TOTAL Income //
        $authUser = auth()->user()->id;

        $totalEarning = Payment::where('user_id', $authUser)->sum('amount');

        // DAILY INCOME //

        $dailyEarning = Payment::where('user_id', $authUser)
            ->whereDate('created_at', Carbon::today())
            ->select(DB::raw('SUM(amount) as totalAmount'))
            ->get();

        // WEEKLY TOTAL INCOME //

        $weeklyTotalSum = Payment::where('user_id', $authUser)
            ->select(
                DB::raw('(SUM(amount)) as total_amount')
            )
            ->whereYear('created_at', date('Y'))
            ->get()
            ->sum('total_amount');

        // MONTHLY TOTAL INCOME //

        $monthlySumAmount = Payment::where('user_id', $authUser)
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('n'))
            ->sum('amount');

        // YEARLY TOTAL INCOME //

        $yearlySumAmount = Payment::where('user_id', $authUser)
            ->whereYear('created_at', date('Y'))
            ->sum('amount');

        return response()->json([
            'total earning' => $totalEarning,
            'daily earning' => $dailyEarning,
            'total week earning' => $weeklyTotalSum,
            'total month earning' => $monthlySumAmount,
            'total yearly earning' => $yearlySumAmount,
        ]);
    }

    public function WeeklyIncome()
    {
        // TOTAL WEEK HISTORY //

        $authUser = auth()->user()->id;

        $weeklyIncome = Payment::where('user_id', $authUser)
            ->select(
                DB::raw('(SUM(amount)) as total_amount'),
                DB::raw('DAYNAME(created_at) as Dayname')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('Dayname')
            ->get()
            ->toArray();

        return response()->json([
            'status' => 'success',
            'week earning' => $weeklyIncome,
            'data' => $this->earningStory()
        ]);
    }

    public function MonthlyIncome()
    {
        $authUser = auth()->user()->id;
        $monthIncom = Payment::where('user_id', $authUser)
            ->select(
                DB::raw('(SUM(amount)) as count'),
                DB::raw('MONTHNAME(created_at) as month_name')
            )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month_name')
            ->get()
            ->toArray();

        return response()->json([
            'status' => 'success',
            'week earning' => $monthIncom,
            'data' => $this->earningStory()
        ]);
    }

    public function Last7YearsIncome(Request $request)
    {
        $year = $request->year;
        $authUser = auth()->user()->id;
        $last7YearsTotal = Payment::where('user_id', $authUser)
            ->where('created_at', '>=', now()->subYears($year))
            ->sum('amount');

        $last7YearsIncome = Payment::where('user_id', $authUser)
            ->select(
                DB::raw('(SUM(amount)) as total_amount'),
                DB::raw('YEAR(created_at) as year')
            )
            ->where('created_at', '>=', now()->subYears($year))
            ->groupBy('year')
            ->get()
            ->toArray();

        return response()->json([
            'status' => 'success',
            'week earning' => $last7YearsIncome,
            'data' => $this->earningStory()
        ]);
    }
}
