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
    public function initialize()
    {
        // This generates a payment reference
        $reference = Flutterwave::generateReference();

        // Enter the details of the payment
        $data = [
            'payment_options' => 'card,banktransfer',
            'amount' => 500,
            'email' => request()->email,
            'tx_ref' => $reference,
            'currency' => 'KES',
            'redirect_url' => route('callback'),
            'customer' => [
                'email' => request()->email,
                'phone_number' => request()->phone,
                'name' => request()->name
            ],
            'meta' => [
                'user_id' => auth()->user()->id,
            ],
            'customizations' => [
                'title' => 'Buy Me Coffee',
                'description' => 'Let express love of coffee'
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
                ]);
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

    // ===================EARNING SECTION=================== //

    public function earningStory()
    {
        $authUser = auth()->user()->id;
        $provider = Provider::where('user_id', $authUser)->first();
        $providerId = $provider->id;
        $booking = Booking::where('provider_id', $provider)->first();
        $bookingId = $booking->id;

        // IN TOTAL Income //
        // $check = UserPayment::where(use)

        $totalEarning = UserPayment::where('booking_id', $bookingId)->sum('amount');

        // DAILY INCOME //

        $dailyEarning = UserPayment::whereDate('created_at', Carbon::today())
            ->select(DB::raw('SUM(amount) as dayly_income'))
            ->get();

        // WEEKLY TOTAL INCOME //

        $weeklyTotalSum = UserPayment::select(
            DB::raw('(SUM(amount)) as total_amount')
        )
            ->whereYear('created_at', date('Y'))
            ->get()
            ->sum('total_amount');

        // MONTHLY TOTAL INCOME //

        $monthlySumAmount = UserPayment::where('user_id', $authUser)
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('n'))
            ->sum('amount');

        // YEARLY TOTAL INCOME //

        $yearlySumAmount = UserPayment::where('user_id', $authUser)
            ->whereYear('created_at', date('Y'))
            ->sum('amount');

        return response()->json([
            'total_earning' => $totalEarning,
            'daily_earning' => $dailyEarning,
            'total_week_earning' => $weeklyTotalSum,
            'total_month_earning' => $monthlySumAmount,
            'total_yearly_earning' => $yearlySumAmount,
        ]);
    }

    public function WeeklyIncome()
    {
        // TOTAL WEEK HISTORY //

        $authUser = auth()->user()->id;

        $weeklyIncome = UserPayment::where('user_id', $authUser)
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
            'week_earning' => $weeklyIncome,
            'data' => $this->earningStory()
        ]);
    }

    public function MonthlyIncome()
    {
        $authUser = auth()->user()->id;
        $monthIncom = UserPayment::where('user_id', $authUser)
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
            'week_earning' => $monthIncom,
            'data' => $this->earningStory()
        ]);
    }

    public function Last7YearsIncome(Request $request)
    {
        $year = $request->year;
        $authUser = auth()->user()->id;
        $last7YearsTotal = UserPayment::where('user_id', $authUser)
            ->where('created_at', '>=', now()->subYears($year))
            ->sum('amount');

        $last7YearsIncome = UserPayment::where('user_id', $authUser)
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
            'week_earning' => $last7YearsIncome,
            'data' => $this->earningStory()
        ]);
    }
}
