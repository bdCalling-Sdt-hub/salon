<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPaymentMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->user_type == 'provider' && auth()->user()->user_status == 1 ){
            return $next($request);
        }
        return response()->json('Subscription is not complete');
    }
}
