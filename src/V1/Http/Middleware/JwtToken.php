<?php

namespace Jet\Publicam\JetEngage\V1\Http\Middleware;

use Auth;
use Input;
use Closure;
//use JWTAuth;
use Exception;
use Illuminate\Http\Request;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Parser;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;

class JwtToken
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

            $getAuth = null;

            $getAuth = $request->header('authorization');

            if ($getAuth !== null) {
                /*
                 * Extract the token from the Bearer
                 */
                list($jwt) = sscanf($getAuth, 'Bearer %s');
            }

            if(!isset($jwt) || $jwt == '') {
                $errorResponse = [
                    'code' => 403,
                    'status' => 'failed',
                    'message' => "Forbidden Token"
                ];

                return \Response::json($errorResponse);
            }

            $signer = new Sha256();

            $token = (new Parser())->parse($jwt);

            $data = new ValidationData();

            if (
                $token->verify($signer, env('SHA_KEY')) === false
            ) {
                $errorResponse = [
                    'code' => 403,
                    'status' => 'failed',
                    'message' => "Invalid Token signature"
                ];

                return \Response::json($errorResponse);
            }

            if(
                $token->validate($data) === false
            ) {
                $errorResponse = [
                    'code' => 403,
                    'status' => 'failed',
                    'message' => "Invalid Token"
                ];

                return \Response::json($errorResponse);
            }

            return $next($request);

        } catch (\Throwable $exception) {
            $errorResponse = [
                'code' => 400,
                'status' => 'failed',
                'message' => "Something went wrong. Please try again.",
                'error_message' => $exception->getMessage()
            ];

            return \Response::json($errorResponse);
        }

    }
}
