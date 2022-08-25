<?php

namespace App\Http\Middleware;

use Closure;

class checkAccess
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
        $user = $request->user();

        if ($user->type == 0 ) {
            return $next($request);
        }

        return redirect('logout');
    }
}
