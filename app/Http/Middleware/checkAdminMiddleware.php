<?php

namespace App\Http\Middleware;

use App\Models\LoginActivity;
use Closure;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;
use Symfony\Component\HttpFoundation\Response;

class checkAdminMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {

        if (auth()->check() && auth()->user()->user_type == 'admin') {
            return $next($request);
        }
        return response()->json('Unauthorized user');

    }
    public function getLocation(){
        return "Dhaka Bangladesh";
    }
}
