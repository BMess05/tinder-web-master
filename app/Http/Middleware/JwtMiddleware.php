<?php

namespace App\Http\Middleware;

use Closure;
use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
class JwtMiddleware  extends BaseMiddleware
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
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if( !$user ) {
                return response()->json(['success' => false, 'message' => 'User not found','status_code'=> 401],401);
            } // throw new Exception('User Not Found');
            else 
            {
                if($user->is_blocked == 1) {
                    return response()->json(['success' => false, 'message' => 'Your account is blocked.','status_code'=> 401]);
                }
            }
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response()->json(['success' => false, 'message' => 'Token Invalid','status_code'=> 401],401);
               
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json(['success' => false, 'message' => 'Token Expired','status_code'=> 401],401);

            }   else {
                if( $e->getMessage() === 'User Not Found') {
                    return response()->json(['success' => false, 'message' => 'User not found','status_code'=> 401],401);
                }
                return response()->json(['success' => false, 'message' => 'Authorization Token not found','status_code'=> 401],401);
            }
        }   
        return $next($request);
    }
}
