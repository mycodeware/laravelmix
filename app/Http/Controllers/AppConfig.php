<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Sql\Configuration;
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Utility\Utils;
use App\Http\Controllers\BaseController;

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
                "message" => ErrorMessageConstants::ERROR_MESSAGES[$language]['invalidPayload'] ?? ErrorMessageConstants::ERROR_MESSAGES[$this->defaultMessageLanugage]['invalidPayload']
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

        $messages = [
            'required' => 'Request cannot be handled due to missing :attribute value',
        ];

        $rule = [
            'package_name' => 'required',
            'locale' => 'required',
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

        $language = strtoupper($params['locale']['language']) ?? '';
        $packageName = $params['package_name'];
        $platform = $params['locale']['platform'];

        $keyName = $packageName.'-'.$platform;

        $configuration = Redis::get($keyName);

        if ($configuration !== null) {
            $responseConfig = Utils::jsonDecode($configuration, true);
        }

        if (! isset($responseConfig) || empty($responseConfig)) {
            $errorResponse = [
                'code' => 400,
                'status' => 'failed',
                'message' => 'No Configuration for provided package.'
            ];

            $this->logger->error(Utils::json($params), $errorResponse);

            return $errorResponse;
        }

        $response = [
            'code' => 200,
            'message' => 'success',
            'status' => 'success',
            'data' => json_decode($configuration, true)
        ];

        $this->logger->info(Utils::json($params), $response);

        return $response;
    }
}
