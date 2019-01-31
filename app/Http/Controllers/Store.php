<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use config;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Sql\Configuration;
use Illuminate\Support\Facades\Redis;
use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;

//use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;

class Store extends BaseController
{
    use HomeTrait;

    public function __construct()
    {
        parent::__construct('store');
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

        $language = strtoupper($payload['locale']['language'] ?? '');

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
            'superStoreId' => 'required',
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

        $language = strtoupper($params['locale']['language'] ?? '');
        $platform = $params['locale']['platform'];

        $superStoreId = $params['superStoreId'];

        try {
            $platformParams = [
                'super_store_id' => $superStoreId,
                'locale' => $params['locale']
            ];

            $platformResponse = $this->callPublicamPlatform(
                $superStoreId,
                config('v1.platformApi.StoreListGroup'),
                $platformParams,
                $this->logger
            );

            if ($platformResponse['code'] !== 200) {
                $this->logger->error(Utils::json($params), $platformResponse);
                return $platformResponse;
            }


        } catch (\Throwable $exception) {
            $error = [
                'code' => 400,
                'status' => 'Failed',
                'message' => ErrorMessageConstants::ERROR_MESSAGES[$language]['somethingWrong'] ?? ErrorMessageConstants::ERROR_MESSAGES[$this->defaultMessageLanugage]['somethingWrong'],
                'error_message' => $exception->getMessage()
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        if(isset($platformResponse['data']) && !empty($platformResponse['data'])) {
            $response = [
                'code' => 200,
                'status' => 'success',
                'message' => 'success',
                'data' => $platformResponse['data']
            ];

            $this->logger->info(Utils::json($params), $response);

            return $response;
        }

        $error = [
            'code' => 400,
            'status' => 'failed',
            'message' => ErrorMessageConstants::ERROR_MESSAGES[$language]['NoData'] ?? ErrorMessageConstants::ERROR_MESSAGES[$this->defaultMessageLanugage]['NoData']
        ];

        $this->logger->error(Utils::json($params), $error);

        return $error;

    }

    /**
     * Get Application Configuration
     * @return Response
     */
    public function getPage(Request $request)
    {
        $payload = $request->all();
        /**
         * Payload Decryption
         */
        $decryptedPayload  = $this->decryptPayload($payload);

        $language = strtoupper($payload['locale']['language'] ?? '');

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
            'superStoreId' => 'required',
            'storeId' => 'required',
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

        $language = strtoupper($params['locale']['language'] ?? '');
        $platform = $params['locale']['platform'];

        $superStoreId = $params['superStoreId'];
        $storeId = $params['storeId'];

        try {
            $platformParams = [
                'super_store_id' => $superStoreId,
                'store_id' => $storeId,
                'locale' => $params['locale']
            ];

            $platformResponse = $this->callPublicamPlatform(
                $superStoreId,
                config('v1.platformApi.PageList'),
                $platformParams,
                $this->logger
            );

            if ($platformResponse['code'] !== 200) {
                $this->logger->error(Utils::json($params), $platformResponse);
                return $platformResponse;
            }


        } catch (\Throwable $exception) {
            $error = [
                'code' => 400,
                'status' => 'Failed',
                'message' => ErrorMessageConstants::ERROR_MESSAGES[$language]['somethingWrong'] ?? ErrorMessageConstants::ERROR_MESSAGES[$this->defaultMessageLanugage]['somethingWrong'],
                'error_message' => $exception->getMessage()
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        if(isset($platformResponse['data']) && !empty($platformResponse['data'])) {
            $response = [
                'code' => 200,
                'status' => 'success',
                'message' => 'success',
                'data' => $platformResponse['data']
            ];

            $this->logger->info(Utils::json($params), $response);

            return $response;
        }

        $error = [
            'code' => 400,
            'status' => 'failed',
            'message' => ErrorMessageConstants::ERROR_MESSAGES[$language]['NoData'] ?? ErrorMessageConstants::ERROR_MESSAGES[$this->defaultMessageLanugage]['NoData']
        ];

        $this->logger->error(Utils::json($params), $error);

        return $error;

    }
}
