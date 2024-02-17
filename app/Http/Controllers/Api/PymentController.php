<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\UserPayment;
use EdwardMuss\Rave\Facades\Rave as Flutterwave;
use Illuminate\Http\Request;
use Carbon;
use DB;

class PymentController extends Controller
{
    // ===================EARNING SECTION=================== //

    public function earningStory()
    {
        $authUser = auth()->user()->id;
        $provider = Provider::where('user_id', $authUser)->first();
        $providerId = $provider->id;
        // $booking = Booking::where('provider_id', $provider)->first();
        // $bookingId = $booking->id;

        // IN TOTAL Income //
        // $check = UserPayment::where(use)

        $totalEarning = UserPayment::where('provider_id', $providerId)->sum('amount');

        // DAILY INCOME //

        $dailyEarning = UserPayment::where('provider_id', $providerId)
            ->whereDate('created_at', Carbon::today())
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

        $monthlySumAmount = UserPayment::where('provider_id', $providerId)
            ->whereYear('created_at', date('Y'))
            ->whereMonth('created_at', date('n'))
            ->sum('amount');

        // YEARLY TOTAL INCOME //

        $yearlySumAmount = UserPayment::where('provider_id', $providerId)
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
        $provider = Provider::where('user_id', $authUser)->first();
        if (!$provider) {
            return response()->json([
                'message' => 'Provider does not exist',
                'status' => 'success',
                'week_earning' => [],
                'data' => []
            ]);
        }
        $providerId = $provider->id;

        $weeklyIncome = UserPayment::where('provider_id', $providerId)
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
        $provider = Provider::where('user_id', $authUser)->first();

        $providerId = $provider->id;
        $monthIncom = UserPayment::where('provider_id', $providerId)
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

    public function Last7YearsIncome()
    {
        $authUser = auth()->user()->id;
        $provider = Provider::where('user_id', $authUser)->first();
        $providerId = $provider->id;
        $last7YearsTotal = UserPayment::where('provider_id', $providerId)
            ->where('created_at', '>=', now()->subYears())
            ->sum('amount');

        $last7YearsIncome = UserPayment::where('provider_id', $providerId)
            ->select(
                DB::raw('(SUM(amount)) as total_amount'),
                DB::raw('YEAR(created_at) as year')
            )
            ->where('created_at', '>=', now()->subYears())
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
