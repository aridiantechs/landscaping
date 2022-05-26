<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\ApiResponser;

class IsEndUser
{
    use ApiResponser;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if(\Auth::check() && \Auth::user()->hasRole("endUser") ){ // endUser
            return $next($request);
        }else{
            return $this->sendResponse([], 'You are not authorized to access this resource.', 400);
        }
        
    }
}
