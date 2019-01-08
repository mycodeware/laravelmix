<?php

namespace Modules\V1\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use JWTAuth;
use Exception;
use Input;
use Auth;

class JwtAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {

            $user = JWTAuth::toUser($request->input('token'));
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                //return response()->json(['error'=>'Token is Invalid']);
                return response()->json([ "status"=>0,'code'=>401,"message"=>"Access token is invalid!" ,'data' => [] ]);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response()->json([ "status"=>0,'code'=>401,"message"=>"Access token is expired!" ,'data' => []]);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenBlacklistedException){
                return response()->json([ "status"=>0,'code'=>401,"message"=>"Unauthorised access!" ,'data' => [] ]);
            }else{
                return response()->json([ "status"=>0,'code'=>401,"message"=>"Access token is required!" ,'data' => [] ]);
            }
        }
        return $next($request);
    }
}
