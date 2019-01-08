<?php

namespace Modules\V1\Http\Middleware;

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


class JwtAuthMiddleware
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
            // header
            $device_id      =  $request->header('DEVICE_ID');
            $package_name   =  $request->header('PACKAGE_NAME');
            $app_signature  =  $request->header('APP_SIGNATURE');

            if($request->headers->has('DEVICE_ID')){
                
            }elseif($request->headers->has('PACKAGE_NAME')){ 

            }elseif($request->headers->has('APP_SIGNATURE')){ 
                
            }else{
               $message = "Required header is missing"; 

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
          
            
        } catch (Exception $e) {
          
           // dd($e->getMessage());
             
        }
        return $next($request);
    }

    public function generateToken(){

          // Configures the issuer (iss claim) 
            $setIssuer  = url('/'); //"http://example.com";
            // Configures the audience (aud claim)
            $setAudience  = \Request::getClientIp(true);//"http://example.org";
            
            $token  =  (new Builder())
                        ->setIssuer($setIssuer) 
                        ->setAudience($setAudience) 
                        ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
                        ->setIssuedAt(time()) // Configures the time that the token was issued (iat claim)
                        ->setNotBefore(time() + 60) // Configures the time that the token can be used (nbf claim)
                        ->setExpiration(time() + 3600) // Configures the expiration time of the token (exp claim)
                        ->set('uid', 1) // Configures a new claim, called "uid"
                        ->getToken(); // Retrieves the generated token 
            $token; // The string representation of the object is a JWT string (pretty easy, right?)

            return Response::json(array(
                    'status' => 'failed',
                    'code' => 200,
                    'message' => "success",
                    'data'  =>  [
                                "token"=>$token
                            ]
                    )
                );
            
    }
}
