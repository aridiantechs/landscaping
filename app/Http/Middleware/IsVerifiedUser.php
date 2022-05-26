<?php

namespace App\Http\Middleware;

use Closure;

class IsVerifiedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(\Auth::check() && \Auth::user()->email_verified_at != null ){ // endUser
            return $next($request);
        }else{
            return $request->expectsJson()
                    ? response()->json([
                        'response_code' => 401,
                        'data' => [],
                        'message' => 'Email not verified.',
                        'errors' => []
                        ], 401)
                    : redirect()->back();
        }
        
    }
}
