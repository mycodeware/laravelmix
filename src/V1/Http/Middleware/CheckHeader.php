<?php

namespace Jet\Publicam\JetEngage\V1\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Exception;
use Input;
use Auth;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser; 
use Validator;
use Response; 

class checkHeader
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
        $device_id      =  $request->header('DEVICE_ID');
        $package_name   =  $request->header('PACKAGE_NAME');
        $app_signature  =  $request->header('APP_SIGNATURE');

        if(!$request->headers->has('DEVICE_ID')){
           $message = "DEVICE ID header is missing"; 
        }elseif(!$request->headers->has('PACKAGE_NAME')){ 
            $message = "PACKAGE NAME header is missing"; 
        }elseif(!$request->headers->has('APP_SIGNATURE')){ 
            $message = "APP SIGNATURE header is missing";   
        }else{
           //
        }   
        if(isset($message)){
             return Response::json(array(
                'status' => 'failed',
                'code' => 204,
                'message' => $message,
                'data'  =>  []
                )
            );
        }

        $messages = [
                'platform.required' => 'Please enter platform name!',
                'version.required' => 'Please enter version!',
                'country.required' => 'Please enter country name!',
                'language.required' => 'Please enter language!'
            ];


            $validator = Validator::make($request->all(), [
                'platform' => 'required',
                'version' => 'required',
                'country' => 'required',
                'language' => 'required'
            ],$messages);

             // Return Error Message
            if ($validator->fails()) {
                        $error_msg  =   [];
                foreach ( $validator->messages()->all() as $key => $value) {
                            array_push($error_msg, $value);     
                        }
                                
                return Response::json(array(
                    'status' => 'failed',
                    'code' => 401,
                    'message' => $error_msg,
                    'data'  =>  []
                    )
                );
            }
  
        return $next($request); 
    }
}
