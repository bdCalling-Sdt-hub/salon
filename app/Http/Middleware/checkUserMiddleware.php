<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->user_type == 'user') {
            return $next($request);
        }
        return response()->json('Unauthorized user',401);
    }
}
