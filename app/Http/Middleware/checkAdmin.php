<?php

namespace App\Http\Middleware;

use Closure;

class checkAdmin
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
        // dd($user);
        if ( $user->deleted_at == null && ($user->type == 3 || $user->type == 0)):
            return $next($request);
        else:
            \Auth::logout();
            \Session::flush();
            \Session::regenerate();
            return redirect('login')->with(['status' => 'danger', 'message' => 'This account is deleted. Please contact support.']);
        endif;

        return redirect('logout')->with(['status' => 'danger', 'message' => 'Invalide Type']); 
    }
}
