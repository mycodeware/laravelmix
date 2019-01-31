<?php

namespace Jet\Publicam\JetEngage\V2\Http\Middleware;

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
