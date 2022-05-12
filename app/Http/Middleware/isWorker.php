<?php

namespace App\Http\Middleware;

use Closure;
use App\Traits\ApiResponser;
use Illuminate\Support\Facades\Response;

class isSupplier
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
        if(\Auth::check() && \Auth::user()->hasRole("worker") ){ 
            return $next($request);
        }else{
            return $request->expectsJson()
                    ? $this->sendError('Error.',"Access Denied.")
                    : redirect()->back();
        }
    }
}
