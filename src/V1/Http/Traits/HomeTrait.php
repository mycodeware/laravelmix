<?php

namespace Jet\Publicam\JetEngage\V1\Http\Traits;

use config;
use Illuminate\Support\Facades\Redis;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;

/**
 * Home Trait
 *
 * @license         <add Licence here>
 * @link            www.jetsynthesys.com
 * @author          Imran Khan <imran.khan@jetsynthesys.com>
 * @since           Jan 09, 2019
 * @copyright       2016 Jetsynthesys Pvt Ltd.
 * @version         1.0
 */

 /**
 * Trait HomeTrait
 * @package Jet\Publicam\JetEngage\V1\Http\Traits
 */
trait HomeTrait
{
    /** Platform API Call Counter to omit infinite loop */
    private static $hitCounter = 0;

    /**
     * Publicam Platform API Call
     *
     * @param array $params
     * @return array
     */
    public function callPublicamPlatform(
        int $superStoreId,
        string $endPoint,
        array $payload,
        $logger
    )
    {

        try {
            $httpClient = new \GuzzleHttp\Client();

            $definedErrors = config('v1.platformAuthError');

            $authParmas=[
                'store_id'=> $superStoreId,
                'expiry'  => env('JWT_TOKEN_EXPIRY_TIME')
            ];

            $response = Self::platformAuthentication(
                $authParmas,
                $logger,
                $httpClient
            );

            if (isset($response['code']) && $response['code'] === 200) {

                $platformResponse = $httpClient->request(
                    'POST',
                    env('PLATFORM_API_BASE_URL').$endPoint,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer '.$response['data']['jwt'],
                        ],
                        'json' => $payload,
                        'http_errors' => false,
                        //'debug' => true
                    ]
                );

                $platformResponseData = Utils::jsonDecode($platformResponse->getBody()->getContents(), true);

                if (
                    ! isset($platformResponseData) ||
                    $platformResponseData == "" ||
                    $platformResponseData == null
                ) {
                    return [
                        "code" => 400,
                        "status" => "failed",
                        "message" => "Someting went wrong. Please try again.",
                        "error_message" => $platformResponse->getBody()->getContents()
                    ];
                }

                if (isset($platformResponseData['code']) && $platformResponseData['code'] !== "") {

                    if ($platformResponseData['code'] === 200) {

                        return $platformResponseData;

                    } elseif (
                        (
                            $definedErrors['invalidAuthKey'] == $platformResponseData['code'] &&
                            $definedErrors['authInvalidMessage'] == $platformResponseData['message']
                        ) ||
                        (
                            $definedErrors['authKeyExpired'] == $platformResponseData['code'] &&
                            $definedErrors['authExpiredMessage'] == $platformResponseData['message']
                        )
                    ) {
                        Self::removeJwtkeyFromRedis($superStoreId);

                        static::$hitCounter = static::$hitCounter + 1;

                        if (static::$hitCounter >= Constants::PLATFORM_MAX_HIT_COUNTER) {
                            $error = [
                                'code'          => $platformResponseData['code'],
                                'status'        => 'failed',
                                'message'       => $platformResponseData['message'] ?? 'Someting went wrong. Please try again.',
                                'error_message' => $platformResponseData['error_message'] ?? ''
                            ];

                            return $error;
                        }

                        return $this->callPublicamPlatform(
                            $superStoreId,
                            $endPoint,
                            $payload,
                            $logger
                        );

                    } else {
                        $error = [
                            'code'          => $platformResponseData['code'],
                            'status'        => 'failed',
                            'message'       => $platformResponseData['message'] ?? 'Someting went wrong. Please try again.',
                            'error_message' => $platformResponseData['error_message'] ?? ''
                        ];

                        return $error;
                    }
                }

                return [
                    "code" => 400,
                    "status" => "failed",
                    "message" => "Someting went wrong. Please try again.",
                    "error_message" => $platformResponse->getBody()->getContents()
                ];
            }

            $error = [
                'code'          => $response['code'] ?? 400,
                'status'        => $response['status'] ?? 'failed',
                'message'       => $response['message'] ?? 'Someting went wrong. Please try again.',
                'error_message' => $response['error_message'] ?? ''
            ];

            return $error;

        } catch (\Throwable $exception) {

            return [
                "code" => 400,
                "status" => "failed",
                "message" => "Someting went wrong. Please try again.",
                "error_message" => $exception->getMessage()
            ];
        }
    }

    /**
     * Retrieve jwt_secret_key
     *
     * @api
     * @version 1.0
     * @param array $authParmas
     * @param object $logger
     * @param object $httpClient
     * @param object $redisClient
     * @return array
     */
    public static function platformAuthentication(
        array $authParmas,
        $logger,
        $httpClient
    ) {
        try {
            $jwtRedisKey = 'PLATFORM-JWT-TOKEN-'.$authParmas['store_id'];

            $jwtKey = Redis::get($jwtRedisKey);

            if(
                $jwtKey !== null &&
                !empty($jwtKey)
            ){
                $response = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Authentication jwt key is available in config.',
                    'data' => [
                        'jwt' => $jwtKey
                    ]
                ];

                $logger->info(Utils::json($authParmas), $response);
                return $response;
            }

            $publicamAuth = Utils::getPlatformDetail('PublicamAuth');

            $platformResponse = $httpClient->request(
                'POST',
                env('PLATFORM_API_BASE_URL').config('v1.platformApi.AuthToken'),
                [
                    'headers' => [
                        'Authorization' => 'Bearer '.env('CONSUMER_KEY').':'.env('SHARED_SECRET_KEY')
                    ],
                    'json' => $authParmas,
                    'http_errors' => false,
                ]
            );

            $response = Utils::jsonDecode($platformResponse->getBody()->getContents(),true);

            if (isset($response['code']) && $response['code'] === 200) {

                Redis::set($jwtRedisKey, $response['data']['jwt']);

                $response = [
                    'code' => 200,
                    'status' => 'success',
                    'message' => 'Authentication jwt key is available in config.',
                    'data' => [
                        'jwt' => $response['data']['jwt']
                    ]
                ];

                $logger->info(Utils::json($authParmas), $response);
                return $response;

            }

            $error = [
                'code' => $response['code'],
                'status' => $response['status'],
                'message' => $response['message'] ?? '',
                'error_message' =>$response['error_message'] ?? ''
            ];

            $logger->error(Utils::json($authParmas), $error);
            return $error;

        } catch (\Throwable $exception){
            $error = [
                'code' => 603,
                'status' => 'failed',
                'message' => 'Unable to fetch data from platform api.',
                'error_message' => $exception->getMessage()
            ];
            $logger->error(Utils::json($authParmas), $error);
            return $error;
        }
    }

    /**
     * remove jwt token from redis
     *
     * @api
     * @version 1.0
     * @param number $superStoreId
     */
    public static function removeJwtkeyFromRedis($superStoreId)
    {
        $jwtRedisKey = 'PLATFORM-JWT-TOKEN-'.$superStoreId;
        Redis::del($jwtRedisKey);
    }

    /**
     *  Call JetPay API
     *
     * @param array $params
     * @return array
     */
    public function callJetPayFormData(
        string $endPoint,
        array $payload,
        $header = null
    )
    {
        try {

            $httpClient = new \GuzzleHttp\Client();

            if ($header !== null) {
                $jetPayResponse = $httpClient->request(
                    'POST',
                    $endPoint,
                    [
                        'headers' => $header,
                        'form_params' => $payload,
                        'http_errors' => false,
                        //'debug' => true
                    ]
                );

            } else {

                $jetPayResponse = $httpClient->request(
                    'POST',
                    $endPoint,
                    [
                        'form_params' => $payload,
                        'http_errors' => false,
                        //'debug' => true
                    ]
                );

            }

            $jetPayResponseData = Utils::jsonDecode($jetPayResponse->getBody()->getContents(), true);

            if (
                ! isset($jetPayResponseData) ||
                $jetPayResponseData == "" ||
                $jetPayResponseData == null
            ) {
                return [
                    "code" => 400,
                    "status" => "failed",
                    "message" => "Someting went wrong. Please try again.",
                    "error_message" => $jetPayResponseData->getBody()->getContents()
                ];
            }

            return $jetPayResponseData;

        } catch (\Throwable $exception) {

            return [
                "code" => 400,
                "status" => "failed",
                "message" => "Someting went wrong. Please try again.",
                "error_message" => $exception->getMessage()
            ];
        }
    }

    /**
     * Publicam Platform API Call With Multipart Type
     *
     * @request object
     * @return array
     */
    public function callPublicamPlatformMultipart(
        int $superStoreId,
        string $endPoint,
        array $payload,
        $logger
    )
    {
        try {

            $httpClient = new \GuzzleHttp\Client();

            $definedErrors = config('v1.platformAuthError');

            $authParmas=[
                'store_id'=> $superStoreId,
                'expiry'  => env('JWT_TOKEN_EXPIRY_TIME')
            ];

            $response = Self::platformAuthentication(
                $authParmas,
                $logger,
                $httpClient
            );

            if (isset($response['code']) && $response['code'] === 200) {

                $platformResponse = $httpClient->request(
                    'POST',
                    env('PLATFORM_API_BASE_URL').$endPoint,
                    [
                        'headers' => [
                            'Authorization' => 'Bearer '.$response['data']['jwt'],
                        ],
                        'multipart' => $payload
                    ]
                );

                $platformResponseData = Utils::jsonDecode($platformResponse->getBody()->getContents(), true);

                if (
                    ! isset($platformResponseData) ||
                    $platformResponseData == "" ||
                    $platformResponseData == null
                ) {
                    return [
                        "code" => 400,
                        "status" => "failed",
                        "message" => "Someting went wrong. Please try again.",
                        "error_message" => $platformResponse->getBody()->getContents()
                    ];
                }

                if (isset($platformResponseData['code']) && $platformResponseData['code'] !== "") {

                    if ($platformResponseData['code'] === 200) {

                        return $platformResponseData;

                    } elseif (
                        (
                            $definedErrors['invalidAuthKey'] == $platformResponseData['code'] &&
                            $definedErrors['authInvalidMessage'] == $platformResponseData['message']
                        ) ||
                        (
                            $definedErrors['authKeyExpired'] == $platformResponseData['code'] &&
                            $definedErrors['authExpiredMessage'] == $platformResponseData['message']
                        )
                    ) {
                        Self::removeJwtkeyFromRedis($superStoreId);

                        static::$hitCounter = static::$hitCounter + 1;

                        if (static::$hitCounter >= evn('PLATFORM_MAX_HIT_COUNTER')) {
                            $error = [
                                'code'          => $platformResponseData['code'],
                                'status'        => 'failed',
                                'message'       => $platformResponseData['message'] ?? 'Someting went wrong. Please try again.',
                                'error_message' => $platformResponseData['error_message'] ?? ''
                            ];

                            return $error;
                        }

                        return $this->callPublicamPlatform(
                            $superStoreId,
                            $endPoint,
                            $payload,
                            $logger
                        );

                    } else {
                        $error = [
                            'code'          => $platformResponseData['code'],
                            'status'        => 'failed',
                            'message'       => $platformResponseData['message'] ?? 'Someting went wrong. Please try again.',
                            'error_message' => $platformResponseData['error_message'] ?? ''
                        ];

                        return $error;
                    }
                }

                return [
                    "code" => 400,
                    "status" => "failed",
                    "message" => "Someting went wrong. Please try again.",
                    "error_message" => $platformResponse->getBody()->getContents()
                ];
            }

            $error = [
                'code'          => $response['code'] ?? 400,
                'status'        => $response['status'] ?? 'failed',
                'message'       => $response['message'] ?? 'Someting went wrong. Please try again.',
                'error_message' => $response['error_message'] ?? ''
            ];

            return $error;

        } catch (\Throwable $exception) {

            return [
                "code" => 400,
                "status" => "failed",
                "message" => "Someting went wrong. Please try again.",
                "error_message" => $exception->getMessage()
            ];
        }
    }
}
