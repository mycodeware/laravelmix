<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;
use Jet\Publicam\JetEngage\V1\Models\Sql\ContentPlaybackStatus;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Validation;

class Content extends BaseController
{
    use HomeTrait;

    public function __construct()
    {
        parent::__construct('content');
    }

    /**
     * Retrieve Individual Content Detail Data with locale
     *
     * @api
     * @version 1.0
     * @return array
     */
    public function getDetail(Request $request)
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
            'storeId' => 'required',
            'pageId' => 'required',
            'portletId' => 'required',
            'packageId' => 'required',
            'contentId' => 'required',
            'contentType' => 'required',
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
        $portletId = $params['portletId'];
        $packageId = $params['packageId'];
        $contentId = $params['contentId'];
        $contentType = $params['contentType'];

        try {

            $platformParams = [
                'super_store_id' => $superStoreId,
                'store_id' => $storeId,
                'page_id' => $pageId,
                'portlet_id' => $portletId,
                'package_id' => $packageId,
                'content_id' => $contentId,
                'content_type' => $contentType,
                'locale' => $params['locale']
            ];

            $platformResponseContent = $this->callPublicamPlatform(
                $superStoreId,
                config('v1.platformApi.SingleContentDetail'),
                $platformParams,
                $this->logger
            );

            if ($platformResponseContent['code'] !== 200) {
                $this->logger->error(Utils::json($params), $platformResponseContent);
                return $platformResponseContent;
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

        if(isset($platformResponseContent['data']) && !empty($platformResponseContent['data'])) {
            $response = [
                'code' => 200,
                'status' => 'success',
                'message' => 'success',
                'data' => $platformResponseContent['data']
            ];

            $this->logger->info(Utils::json($params), $response);

            return $response;
        }

        $error = [
            'code' => 400,
            'status' => 'failed',
            'message' => Lang::get('v1::messages.NoData'),
        ];

        $this->logger->error(Utils::json($params), $error);

        return $error;
    }

    /**
     * Post Individual Content Playback Duration
     *
     * @api
     * @version 1.0
     * @return array
     */
    public function postPlaybackStatus(Request $request)
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
            'storeId' => 'required',
            'pageId' => 'required',
            'portletId' => 'required',
            'packageId' => 'required',
            'contentId' => 'required',
            'contentType' => 'required',
            'duration' => 'required|integer',
            'totalDuration' => 'required|integer',
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
        $storeId = $params['storeId'];
        $pageId = $params['pageId'];
        $portletId = $params['portletId'];
        $packageId = $params['packageId'];
        $contentId = $params['contentId'];
        $contentType = $params['contentType'];
        $totalDuration = $params['totalDuration'];
        $duration = $params['duration'];

        $seriesId = (isset($params['seriesId']) && $params['seriesId'] !== "") ? $params['seriesId'] + 0 : null;
        $seasonId = (isset($params['seasonId']) && $params['seasonId'] !== "") ? $params['seasonId'] + 0 : null;

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

        try {

            $contentPlayback = ContentPlaybackStatus::where([
                'user_code' => $userCode,
                'content_id' => $contentId
            ])->first();

            if ($contentPlayback !== null) {
                $contentPlayback->duration = $duration;
                $contentPlayback->total_duration = $totalDuration;

            } else {

                $contentPlayback = new ContentPlaybackStatus;
                $contentPlayback->user_code = $userCode;
                $contentPlayback->content_id = $contentId;
                $contentPlayback->duration = $duration;
                $contentPlayback->super_store_id = $superStoreId;
                $contentPlayback->store_id = $storeId;
                $contentPlayback->page_id = $pageId;
                $contentPlayback->portlet_id = $portletId;
                $contentPlayback->total_duration = $totalDuration;

                $contentPlayback->package_id = $packageId;
                $contentPlayback->type = strtolower($contentType);

                if(isset($seriesId) && $seriesId !== 0 && isset($seasonId) && $seasonId !== 0) {
                    $contentPlayback->series_id = $seriesId;
                    $contentPlayback->season_id = $seasonId;
                }
            }

            $contentPlayback->save();

            $response =  [
                'code' => 200,
                'status' => 'success',
                'message' => Lang::get('v1::messages.success')
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

    /**
     * Get Individual Content Playback Duration
     *
     * @api
     * @version 1.0
     * @return array
     */
    public function getPlaybackStatus(Request $request)
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
            'contentId' => 'required',
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
        $contentId = $params['contentId'];

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

        try {

            $contentPlayback = ContentPlaybackStatus::where([
                'user_code' => $userCode,
                'content_id' => $contentId
            ])->first();

            if ($contentPlayback !== null) {

                $response =  [
                    'code' => 200,
                    'status' => 'success',
                    'message' => Lang::get('v1::messages.success'),
                    'data' => [
                        'playback_duration' => ($contentPlayback->duration + 0)
                    ],
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
}
