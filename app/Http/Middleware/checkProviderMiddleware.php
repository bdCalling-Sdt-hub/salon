<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class checkProviderMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->user_type == 'provider') {
            return $next($request);
        }
        return response()->json('Unauthorized user');
    }
}
