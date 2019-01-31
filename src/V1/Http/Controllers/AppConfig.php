<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Sql\Configuration;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redis;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;

//use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;

class AppConfig extends BaseController
{
    public function __construct()
    {
        parent::__construct('app_config');
    }

    /**
     * Get Application Configuration
     * @return Response
     */
    public function get(Request $request)
    {
        $payload = $request->all();

        /**
         * Payload Decryption
         */
        $decryptedPayload  = $this->decryptPayload($payload);

        $language = strtoupper($payload['locale']['language']) ?? '';

        if ($decryptedPayload['code'] !== 200) {
            $error = [
                "code" => 400,
                "status" => "Failed",
                "message" => Lang::get('v1::messages.invalidPayload')
            ];
            $this->logger->error(Utils::json($payload), $error);
            return $error;
        }

        $params = $decryptedPayload['data'];

        //Locale Validation
        $checkLocale = Utils::validateLocale($params);

        if ($checkLocale['code'] !== 200) {
            $this->logger->error(Utils::json($params), $checkLocale);
            return $checkLocale;
        }

        $language = strtolower($params['locale']['language']) ?? '';
        $platform = strtolower($params['locale']['platform']);

        \App::setLocale($language);

        $messages = [
            'required' => Lang::get('v1::messages.required')
        ];

        $rule = [
            'package_name' => 'required',
        ];

        $validator = Validator::make($params, $rule, $messages);

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

        $packageName = $params['package_name'];

        /**
         * Redis Key Name
         */
        $keyName = $packageName.'-'.$platform;

        $configuration = Redis::get($keyName);

        if ($configuration !== null) {
            $responseConfig = Utils::jsonDecode($configuration, true);
        }

        if (! isset($responseConfig) || empty($responseConfig)) {
            $errorResponse = [
                'code' => 400,
                'status' => 'failed',
                'message' => Lang::get('v1::messages.noConfigPackage')
            ];

            $this->logger->error(Utils::json($params), $errorResponse);

            return $errorResponse;
        }

        $response = json_decode($configuration, true);

        /*
        [
            'code' => 200,
            'status' => 'success',
            'message' => Lang::get('v1::messages.success'),
            'data' => json_decode($configuration, true)
        ];
        */

        $this->logger->info(Utils::json($params), $response);

        return $response;
    }
}
