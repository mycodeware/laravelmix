<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use config;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redis;
use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Validation;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;

/**
 * Dashboard Class
 *
 * @library         JetEngage
 *
 * @license         <add Licence here>
 * @link            www.jetsynthesys.com
 * @author          Riyaz Patwegar <riyaz.patwegar@jetsynthesys.com>
 * @since           Jan 24, 2019
 * @copyright       2019 Jetsynthesys Pvt Ltd.
 * @version         1.0
 */
class Dashboard extends BaseController
{
    use HomeTrait;

    public function __construct()
    {
        parent::__construct('dashboard');
    }

    /**
     * Retrieve Dashboard Categories with locale
     *
     * @api
     * @version 1.0
     * @return array
     */
    public function get(Request $request)
    {
        $payload = $request->all();

        /**
         * Payload Decryption
         */
        $decryptResponse = $this->decryptPayload($payload);

        if ($decryptResponse['code'] !== 200) {
            $error = [
                "code" => 400,
                "status" => "Failed",
                "message" => Lang::get('v1::messages.invalidPayload')
            ];
            $this->logger->error(Utils::json($payload), $error);
            return $error;
        }

        $params = $decryptResponse['data'];

        $checkLocale = Utils::validateLocale($params);

        if ($checkLocale['code'] !== 200) {
            $this->logger->error(Utils::json($params), $checkLocale);

            return $checkLocale;
        }

        $language = strtoupper($params['locale']['language']) ?? '';

        \App::setLocale($language);

        $messages = [
            'required' => Lang::get('v1::messages.required')
        ];

        $rule = [
            'superStoreId' => 'required',
            'storeId'   => 'required',
            'pageId' => 'required',
            'page'  =>  'required'
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

        $superStoreId = $params['superStoreId'];
        $storeId = $params['storeId'];
        $pageId = $params['pageId'];

        try {
            $platformParams                  = $params;
            $platformParams['super_store_id']= $superStoreId;
            $platformParams['store_id']      = $storeId;
            $platformParams['page_id']       = $pageId;

            unset($platformParams['superStoreId'], $platformParams['storeId'], $platformParams['pageId']);

            $platformResponse = $this->callPublicamPlatform(
                $superStoreId,
                config('v1.platformApi.DashboardCategory'),
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
                'message' => Lang::get('v1::messages.somethingWrong'),
                'error_message' => $exception->getMessage()
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        if (isset($platformResponse['data']) && !empty($platformResponse['data'])) {
            $response = [
                'code'    => 200,
                'status'  => 'success',
                'message' => $platformResponse['message'],
                'data'    => $platformResponse['data']
            ];

            $this->logger->info(Utils::json($params), $response);

            return $response;
        }

        $error = [
            'code' => 400,
            'status' => 'Failed',
            'message' => Lang::get('v1::messages.NoData')
        ];

        $this->logger->error(Utils::json($params), $error);

        return $error;
    }

}
