<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use Validator;
use Lcobucci\JWT\Builder;
use Tymon\JWTAuth\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Sql\Configuration;
use Illuminate\Support\Facades\Lang;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTFactory;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;

//use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;

class Auth extends BaseController
{
    public function __construct()
    {
        parent::__construct('auth');
    }

    /**
     * Get Application Configuration
     * @return Response
     */
    public function get(Request $request)
    {
        $params = $request->all();

        //Locale Validation
        $checkLocale = Utils::validateLocale($params);

        if ($checkLocale['code'] !== 200) {
            $this->logger->error(Utils::json($params), $checkLocale);
            return $checkLocale;
        }
        $language = strtolower($params['locale']['language']) ?? '';
        $platform = $params['locale']['platform'];

        /**
         * Set Language
         */
        \App::setLocale($language);

        $messages = [
            'required' => Lang::get('v1::messages.requiredHeader'),
        ];

        $rule = [
            'device-id' => 'required',
            'app-signature' => 'required',
            'package-name' => 'required'
        ];

        $validator = Validator::make($request->header(), $rule, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $error) {
                $errorResponse = [
                    'code' => 601,
                    'status' => 'failed',
                    'message' => $error
                ];

                $this->logger->error(Utils::json($params), $errorResponse);

                return $errorResponse;
            }
        }

        $packageName = $request->header('package-name');
        $appSignature = $request->header('app-signature');
        $deviceId = $request->header('device-id');

        if($packageName !== env('APP_PACKAGE_NAME')) {
            $errorResponse = [
                'code' => 601,
                'status' => 'failed',
                'message' => Lang::get('v1::messages.invalidAppPackage')
            ];

            $this->logger->error(Utils::json($params), $errorResponse);

            return $errorResponse;
        }

        // Check App Signature
        if($platform == 'ios' || $platform == 'appletv') {

            if(! in_array($appSignature, config('v1.appSignatureIOS'))) {
                $errorResponse = [
                    'code' => 601,
                    'status' => 'failed',
                    'message' => Lang::get('v1::messages.invalidAppSignature')
                ];

                $this->logger->error(Utils::json($params), $errorResponse);

                return $errorResponse;
            }

        } else {

            if(! in_array($appSignature, config('v1.appSignatureANDROID'))) {
                $errorResponse = [
                    'code' => 601,
                    'status' => 'failed',
                    'message' => Lang::get('v1::messages.invalidAppSignature')
                ];

                $this->logger->error(Utils::json($params), $errorResponse);

                return $errorResponse;
            }
        }

        try {
            $tokenId = base64_encode(openssl_random_pseudo_bytes(32));

            $issuedAt = time();

            // Add $notBefore
            $notBefore = $issuedAt;// + ($notBefore * 60);

            // Adding expiry time (1 day)
            $expire = $notBefore + (env('TOKEN_EXPIRY') * 60);

            /*
            if ($expiry !== null) {
                $expire = $notBefore + $expiry * 60;
            }
            */

            // Retrieve the server name
            $serverName = Utils::getServerHeader('SERVER_NAME');

            $signer = new Sha256();

            $token = (new Builder())
                ->setIssuer($serverName) // Configures the issuer (iss claim)
                ->setId($tokenId) // Configures the id (jti claim), replicating as a header item
                ->setIssuedAt($issuedAt) // Configures the time that the token was issued (iat claim)
                ->setNotBefore($notBefore) // Configures the time that the token can be used (nbf claim)
                ->setExpiration($expire) // Configures the expiration time of the token (exp claim)
                ->sign($signer, env('SHA_KEY', ''))
                //->set('uid', 1) // Configures a new claim, called "uid"
                //->setAudience('http://example.org') // Configures the audience (aud claim)
                ->getToken(); // Retrieves the generated token

            $tokenString = (string) $token;

            /**
             * Get RSA Public Key
             */
            $fileHander = fopen(base_path().'/crt/public.pem', 'r');
            $publicKey = fread($fileHander, 8192);
            fclose($fileHander);

            /**
             * Get Pinning Key
             */
            if(
                isset($_SERVER['HTTP_HOST'])
                && strpos($_SERVER['HTTP_HOST'], env('APP_DOMAIN')) !== false
            ) {
                $pinningKey = config('v1.pinningCrtKey.domain');
            } else {
                $pinningKey = config('v1.pinningCrtKey.open');
            }

            $response = [
                'code' => 200,
                'status' => 'success',
                'message' => Lang::get('v1::messages.success'),
                'data' => [
                    'jwt' => $tokenString,
                    'key' => $publicKey,
                    'cert' => $pinningKey
                ]
            ];

            $this->logger->info(Utils::json($params), $response);

            return $response;

        } catch (\Throwable $exception) {

            $errorResponse = [
                'code' => 400,
                'status' => 'failed',
                'message' => Lang::get('v1::messages.somethingWrong'),
                'error_message' => $exception->getMessage()
            ];

            $this->logger->error(Utils::json($params), $errorResponse);

            return $errorResponse;
        }
    }
}
