<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redis;
use Jet\Publicam\JetEngage\V1\Models\Sql\User;
use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;
use Jet\Publicam\JetEngage\V1\Models\Sql\UserDeviceMappig;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Validation;

class Device extends BaseController
{
    use HomeTrait;

    public function __construct()
    {
        parent::__construct('device');
    }

    /**
     * Validate Device
     *
     * @api
     * @version 1.0
     * @return array
     */
    public function validate(Request $request)
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
        $platform = $params['locale']['platform'];

        \App::setLocale($language);

        $messages = [
            'required' => Lang::get('v1::messages.required'),
        ];

        $rule = [
            'superStoreId' => 'required',
            'userCode' => 'required',
            'deviceId' => 'required',
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
        $userCode = $params['userCode'];
        $deviceId = $params['deviceId'];

        /**
         * Check whether provided user code exists or not
         */
        if (Validation::userExists($superStoreId + 0, $userCode) === false) {
            $error = [
                "code" => 601,
                "status" => "failed",
                "message" => Lang::get('v1::messages.invalidUserCode'),
            ];
            $this->logger->error(Utils::json($params), $error);
            return $error;
        }

        //Check  Device Limit
        $getActiveDeviceCount = UserDeviceMappig::where([
            'super_store_id' => $superStoreId,
            'user_code' => $userCode,
            'is_active' => 1,
        ])->count();

        try {
            $getDeviceLimit = $this->getDeviceLimit($superStoreId + 0,$params['locale']);

            if (
                ! isset($getDeviceLimit['code'])
                || $getDeviceLimit['code'] !== 200
                //|| !isset($getDeviceLimit['data']['is_device_limit_set'])
            ) {
                $this->logger->error(Utils::json($params), $getDeviceLimit);

                return $getDeviceLimit;
            }

        } catch (\Throwable $exception) {
            $error = [
                'code' => 604,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.somethingWrong'),
                'error_message' => $exception->getMessage()
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        if (
            isset($getDeviceLimit['data']['is_device_limit_set'])
            && $getDeviceLimit['data']['is_device_limit_set'] == 1
        ) {

            if ($getActiveDeviceCount > $getDeviceLimit['data']['max_device_count_per_user']) {
                $error = [
                    'code' => 603,
                    'status' => 'Failed',
                    'message' => Lang::get('v1::messages.maxDeviceLimitReached'),
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;
            } else {
                $checkDeviceExists = UserDeviceMappig::where([
                    'super_store_id' => $superStoreId,
                    'user_code' => $userCode,
                    'device_identifier' => $deviceId,
                    'platform' => $platform,
                    //'is_active' => 1,
                ])->first();

                if ($checkDeviceExists === null) {
                    if ($getActiveDeviceCount >= $getDeviceLimit['data']['max_device_count_per_user']) {
                        $error = [
                            'code' => 603,
                            'status' => 'Failed',
                            'message' => Lang::get('v1::messages.maxDeviceLimitReached'),
                        ];

                        $this->logger->error(Utils::json($params), $error);

                        return $error;
                    }
                }
            }

        } elseif (
            isset($getDeviceLimit['data']['is_device_limit_set'])
            && $getDeviceLimit['data']['is_device_limit_set'] == 0
        ) {
            $checkActiveDeviceExists = UserDeviceMappig::where([
                'super_store_id' => $superStoreId,
                'user_code' => $userCode,
                'device_identifier' => $deviceId,
                'platform' => $platform,
                'is_active' => 1,
            ])->first();

            if($checkActiveDeviceExists !== null) {
                $response = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => Lang::get('v1::messages.success'),
                ];

                $this->logger->info(Utils::json($params), $response);

                return $response;
            } else {
                $error = [
                    'code' => 605,
                    'status' => 'Failed',
                    'message' => Lang::get('v1::messages.deviceDosentExists'),
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;
            }
        }

        $checkDeviceExists = UserDeviceMappig::where([
            'super_store_id' => $superStoreId,
            'user_code' => $userCode,
            'device_identifier' => $deviceId,
            'platform' => $platform,
            //'is_active' => 1,
        ])->first();

        if ($checkDeviceExists !== null) {
            if ($checkDeviceExists->is_active === 1) {
                $response = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => Lang::get('v1::messages.success'),
                ];

                $this->logger->info(Utils::json($params), $response);

                return $response;
            } elseif ($checkDeviceExists->is_active === 0) {

                if (
                    isset($getDeviceLimit['data']['is_device_limit_set'])
                    && $getDeviceLimit['data']['is_device_limit_set'] == 1
                ) {

                    if ($getActiveDeviceCount >= $getDeviceLimit['data']['max_device_count_per_user']) {
                        $error = [
                            'code' => 603,
                            'status' => 'Failed',
                            'message' => Lang::get('v1::messages.maxDeviceLimitReached'),
                        ];

                        $this->logger->error(Utils::json($params), $error);

                        return $error;
                    }

                }

                $response = [
                    'code' => 604,
                    'status' => 'success',
                    'message' => Lang::get('v1::messages.deviceRemoveInactive'),
                ];

                $this->logger->info(Utils::json($params), $response);

                return $response;
            }
        }

        $error = [
            'code' => 400,
            'status' => 'Failed',
            'message' => Lang::get('v1::messages.deviceDosentExists'),
        ];

        $this->logger->error(Utils::json($params), $error);

        return $error;
    }

    /**
     * Get User Connected Device List
     *
     * @api
     * @version 1.0
     * @return array
     */
    public function getList(Request $request)
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
        $platform = $params['locale']['platform'];

        \App::setLocale($language);

        $messages = [
            'required' => Lang::get('v1::messages.required'),
        ];

        $rule = [
            'superStoreId' => 'required|integer',
            'userCode' => 'required',
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
        $userCode = $params['userCode'];

        /**
         * Check whether provided user code exists or not
         */
        if (Validation::userExists($superStoreId + 0, $userCode) === false) {
            $error = [
                "code" => 601,
                "status" => "failed",
                "message" => Lang::get('v1::messages.invalidUserCode'),
            ];
            $this->logger->error(Utils::json($params), $error);
            return $error;
        }

        $maxDeviceLimit = 0;

        try {
            $getDeviceLimit = $this->getDeviceLimit($superStoreId,$params['locale']);

            if (
                ! isset($getDeviceLimit['code'])
                || $getDeviceLimit['code'] !== 200
                //|| !isset($getDeviceLimit['data']['is_device_limit_set'])
            ) {
                $this->logger->error(Utils::json($params), $getDeviceLimit);

                return $getDeviceLimit;
            }

        } catch (\Throwable $exception) {
            $error = [
                'code' => 604,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.somethingWrong'),
                'error_message' => $exception->getMessage()
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        if (
            isset($getDeviceLimit['data']['is_device_limit_set'])
            && $getDeviceLimit['data']['is_device_limit_set'] == 1
        ) {
            $maxDeviceLimit = ($getDeviceLimit['data']['max_device_count_per_user'] ?? 0) + 0;
        }

        $getDeviceList = UserDeviceMappig::where([
            'super_store_id' => $superStoreId,
            'user_code' => $userCode,
            'is_active' => 1
        ])->get();

        if (! $getDeviceList->isEmpty()) {
            $getDeviceList = $getDeviceList->toArray();

            $responseData = [];

            foreach ($getDeviceList as $device) {
                $tmp = [];

                $tmp = [
                    'id' => $device['id'],
                    'platform' => $device['platform'],
                    'device_id' => $device['device_identifier'],
                    'device_make' => $device['device_make'] ?? '',
                    'device_model' => $device['device_model'] ?? '',
                    'user_agent' => $device['user_agent'] ?? '',
                    'device_os' => $device['device_os'] ?? '',
                    'is_active' => $device['is_active'],
                    'created_at' => $device['created_at'],
                    'updated_at' => $device['updated_at']
                ];

                $responseData[] = $tmp;
                unset($tmp);
            }

            if (isset($responseData) && ! empty($responseData)) {

                $response = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => Lang::get('v1::messages.success'),
                    'data' => [
                        'device_list' => $responseData
                    ]
                ];

                if(isset($maxDeviceLimit) && $maxDeviceLimit !== 0) {
                    $response['data']['max_allowed_devices'] = $maxDeviceLimit;
                }

                $this->logger->info(Utils::json($params), $response);

                return $response;
            }
        }

        $error = [
            'code' => 400,
            'status' => 'Failed',
            'message' => Lang::get('v1::messages.NoData'),
        ];

        if(isset($maxDeviceLimit) && $maxDeviceLimit !== 0) {
            $response['data']['max_allowed_devices'] = $maxDeviceLimit;
        }

        $this->logger->error(Utils::json($params), $error);

        return $error;
    }

    /**
     * Add Device to User's Connected Device List
     *
     * @api
     * @version 1.0
     * @return array
     */
    public function add(Request $request)
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
        $platform = $params['locale']['platform'];

        \App::setLocale($language);

        $messages = [
            'required' => Lang::get('v1::messages.required'),
        ];

        $rule = [
            'superStoreId' => 'required|integer',
            'userCode' => 'required',
            'deviceId' => 'required',
            'deviceMake' => 'required',
            'deviceModel' => 'required',
            'deviceOS' => 'required',
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
        $userCode = $params['userCode'];
        $deviceId = $params['deviceId'];
        $deviceMake = $params['deviceMake'];
        $deviceModel = $params['deviceModel'];
        $deviceOS = $params['deviceOS'];

        /**
         * Check whether provided user code exists or not
         */
        if (Validation::userExists($superStoreId + 0, $userCode) === false) {
            $error = [
                "code" => 601,
                "status" => "failed",
                "message" => Lang::get('v1::messages.invalidUserCode'),
            ];
            $this->logger->error(Utils::json($params), $error);
            return $error;
        }

        $checkDeviceExists = UserDeviceMappig::where([
            'super_store_id' => $superStoreId,
            'user_code' => $userCode,
            'is_active' => 1,
            'platform' => $platform,
            'device_identifier' => $deviceId
        ])->first();

        if ($checkDeviceExists !== null) {

            $response = [
                'code' => 200,
                'status' => 'success',
                'message' => Lang::get('v1::messages.addDeviceExists')
            ];

            $this->logger->info(Utils::json($params), $response);

            return $response;
        }

        try {
            $getDeviceLimit = $this->getDeviceLimit($superStoreId,$params['locale']);

            if (
                ! isset($getDeviceLimit['code'])
                || $getDeviceLimit['code'] !== 200
                //|| !isset($getDeviceLimit['data']['is_device_limit_set'])
            ) {
                $this->logger->error(Utils::json($params), $getDeviceLimit);

                return $getDeviceLimit;
            }

        } catch (\Throwable $exception) {
            $error = [
                'code' => 604,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.somethingWrong'),
                'error_message' => $exception->getMessage()
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        if (isset($getDeviceLimit['data']['is_device_limit_set']) && $getDeviceLimit['data']['is_device_limit_set'] === 1) {

            $getActiveDeviceCount = UserDeviceMappig::where([
                'super_store_id' => $superStoreId,
                'user_code' => $userCode,
                'is_active' => 1,
            ])->count();

            if ($getActiveDeviceCount >= $getDeviceLimit['data']['max_device_count_per_user']) {
                $error = [
                    'code' => 603,
                    'status' => 'Failed',
                    'message' => Lang::get('v1::messages.maxDeviceLimitReached')
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;
            }

        }

        $getDeviceDetail = UserDeviceMappig::where([
            'super_store_id' => $superStoreId,
            'user_code' => $userCode,
            'device_identifier' => $deviceId,
            'platform' => $platform
        ])->first();

        if ($getDeviceDetail !== null) {

            try {

                $getDeviceDetail->is_active = 1;
                $getDeviceDetail->device_make = $deviceMake;
                $getDeviceDetail->device_model = $deviceModel;
                $getDeviceDetail->device_os = $deviceOS;

                $getDeviceDetail->save();

                $response = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => Lang::get('v1::messages.deviceAddSuccess'),
                ];

                $this->logger->info(Utils::json($params), $response);

                return $response;

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

        } else {

            try {
                $insertDeviceDetails = new UserDeviceMappig;

                $insertDeviceDetails->super_store_id = $superStoreId;
                $insertDeviceDetails->user_code = $userCode;
                $insertDeviceDetails->device_identifier = $deviceId;
                $insertDeviceDetails->platform = $platform;
                $insertDeviceDetails->device_make = $deviceMake;
                $insertDeviceDetails->device_model = $deviceModel;
                $insertDeviceDetails->device_os = $deviceOS;
                $insertDeviceDetails->is_active = 1;

                $insertDeviceDetails->save();

                $response = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => Lang::get('v1::messages.deviceAddSuccess'),
                ];

                $this->logger->info(Utils::json($params), $response);

                return $response;

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
        }

        $error = [
            'code' => 400,
            'status' => 'Failed',
            'message' => Lang::get('v1::messages.somethingWrong')
        ];

        $this->logger->error(Utils::json($params), $error);

        return $error;
    }

    /**
     * Remove Device from User's Connected Device List
     *
     * @api
     * @version 1.0
     * @return array
     */
    public function remove(Request $request)
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
        $platform = $params['locale']['platform'];

        \App::setLocale($language);

        $messages = [
            'required' => Lang::get('v1::messages.required'),
        ];

        $rule = [
            'superStoreId' => 'required|integer',
            'userCode' => 'required',
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
        $userCode = $params['userCode'];

        $removeAll = isset($params['removeAll']) && $params['removeAll'] !== '' ? $params['removeAll'] + 0 : null;
        $deviceId = isset($params['deviceId']) && $params['deviceId'] !== '' ? $params['deviceId'] : null;
        $id = isset($params['id']) && $params['id'] !== '' ? $params['id'] + 0 : null;

        /**
         * Check whether provided user code exists or not
         */
        if (Validation::userExists($superStoreId + 0, $userCode) === false) {
            $error = [
                "code" => 601,
                "status" => "failed",
                "message" => Lang::get('v1::messages.invalidUserCode'),
            ];
            $this->logger->error(Utils::json($params), $error);
            return $error;
        }

        if (isset($removeAll) && $removeAll === 1) {

            $updateMultipleStatus = UserDeviceMappig::where([
                'super_store_id' => $superStoreId,
                'user_code' => $userCode,
                'is_active' => 1
            ])->update(['is_active' => 0]);

            $response = [
                'code' => 200,
                'status' => 'success',
                'message' => Lang::get('v1::messages.deviceRemoveSuccess'),
            ];

            $this->logger->info(Utils::json($params), $response);

            return $response;
        }

        if ($deviceId === null) {
            $error = [
                'code'    => 601,
                'status'  => 'failed',
                'message' => Lang::get('v1::messages.missingDeviceId'),
            ];
            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        if ($id === null || $id == 0) {
            $error = [
                'code'    => 601,
                'status'  => 'failed',
                'message' => Lang::get('v1::messages.missingDeviceId'),
            ];
            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        try {
            $checkDeviceExists = UserDeviceMappig::where([
                'super_store_id' => $superStoreId,
                'user_code' => $userCode,
                'device_identifier' => $deviceId,
                'id' => $id,
            ])->first();

            if ($checkDeviceExists !== null) {
                if ($checkDeviceExists->is_active === 0) {
                    $response = [
                        'code' => 200,
                        'status' => 'success',
                        'message' => Lang::get('v1::messages.deviceRemoveInactive'),
                    ];

                    $this->logger->info(Utils::json($params), $response);

                    return $response;
                }

                $checkDeviceExists->is_active = 0;

                $checkDeviceExists->save();

                $response = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => Lang::get('v1::messages.deviceRemoveSuccess'),
                ];

                $this->logger->info(Utils::json($params), $response);

                return $response;
            }

            $error = [
                'code' => 604,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.deviceDosentExists')
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;

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
    }

    /**
     * Get Device List from Platform
     *
     * @api
     * @version 1.0
     * @return array
     */
    private function getDeviceLimit(
        $superStoreId,
        $locale
    )
    {
        $keyName = "User-Device-Limit-$superStoreId";

        $deviceLimit = Redis::get($keyName);

        if ($deviceLimit !== null) {
            return [
                'code' => 200,
                'message' => 'success',
                'status' => 'success',
                'data' => Utils::jsonDecode($deviceLimit, true)
            ];
        }

        try {
            $platformParams = [
                'super_store_id' => $superStoreId,
                'locale' => $locale
            ];

            $platformResponse = $this->callPublicamPlatform(
                $superStoreId,
                config('v1.platformApi.GetDeviceLimit'),
                $platformParams,
                $this->logger
            );

            if ($platformResponse['code'] !== 200) {
                $this->logger->error(Utils::json($platformParmas), $platformResponse);
                return $platformResponse;
            }

        } catch (\Throwable $exception) {
            $error = [
                'code' => 400,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.somethingWrong'),
                'error_message' => $exception->getMessage()
            ];

            return $error;
        }

        if (
            isset($platformResponse['data'])
            && !empty($platformResponse['data'])
            && isset($platformResponse['data']['is_device_limit_set'])
        ) {
            //Set Redis Key Logic
            $redisLimit = Redis::setex(
                $keyName,
                env('DEVICE_LIMIT_KEY_EXPIRY'), //Constants::DEVICE_LIMIT_KEY_EXPIRY,
                Utils::json($platformResponse['data'])
            );

            $this->logger->info(Utils::json($platformResponse), [$redisLimit]);

            return [
                'code' => 200,
                'message' => 'success',
                'status' => Lang::get('v1::messages.success'),
                'data' => $platformResponse['data']
            ];
        }

        $error = [
            'code' => 400,
            'status' => 'Failed',
            'message' => Lang::get('v1::messages.somethingWrong'),
            'error_message' => $exception->getMessage()
        ];

        return $error;
    }

}
