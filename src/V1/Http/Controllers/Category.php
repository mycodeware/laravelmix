<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use config;
use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;
use Jet\Publicam\JetEngage\V1\Models\Sql\ContentPlaybackStatus;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Validation;

class Category extends BaseController
{
    use HomeTrait;

    public function __construct()
    {
        parent::__construct('category');
    }

    /**
     * Retrieve Individual Content Detail Data with locale
     *
     *
     * Dashboard Categorries List (continue_watching, recommended_movies)
     * Library Categorries List (bought_library, rented_library)
     *
     * @api
     * @version 1.0
     * @return array
     */
    public function getData(Request $request)
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
            'categoryProgramId' => 'required',
            'page' => 'required',
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

        $superStoreId = $params['superStoreId'] + 0;
        $storeId = $params['storeId'] + 0;
        $pageId = $params['pageId'] + 0;
        $pageNumber = $params['page'] + 0;
        $categoryProgramId = $params['categoryProgramId'];
        $userCode = isset($params['userCode']) && $params['userCode'] !== '' ? $params['userCode'] : null;

        if($pageNumber <= 0) {
            $pageNumber = 1;
        }

        if(strtolower($categoryProgramId) === 'continue_watching') {

            if ($userCode === null) {
                $error = [
                    'code'    => 601,
                    'status'  => 'failed',
                    'message' => Lang::get('v1::messages.missingUserCode'),
                ];
                $this->logger->error(Utils::json($params), $error);

                return $error;
            }

            /**
             * Validate user Code
             */
            if (Validation::userExists($superStoreId, $userCode) === false) {
                $error = [
                    'code' => 604,
                    'status' => 'Failed',
                    'message' => Lang::get('v1::messages.UserDosentExists'),
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;
            }

            /**
             * Get Category Data from helper
             */
            try {

                $responseData = $this->continueWatchig(
                    $superStoreId,
                    $storeId,
                    $pageId,
                    $categoryProgramId,
                    $userCode,
                    $pageNumber,
                    $params
                );

                return $responseData;

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

        } elseif (strtolower($categoryProgramId) === 'recommended_movies') {

            /**
             * Get Category Data from helper
             */
            try {

                $responseData = $this->recommended(
                    $superStoreId,
                    $storeId,
                    $pageId,
                    $categoryProgramId,
                    $pageNumber,
                    $params
                );

                return $responseData;

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

        } elseif (strtolower($categoryProgramId) === 'bought_library') {
            if ($userCode === null) {
                $error = [
                    'code'    => 601,
                    'status'  => 'failed',
                    'message' => Lang::get('v1::messages.missingUserCode'),
                ];
                $this->logger->error(Utils::json($params), $error);

                return $error;
            }

            /**
             * Validate user Code
             */
            if (Validation::userExists($superStoreId, $userCode) === false) {
                $error = [
                    'code' => 604,
                    'status' => 'Failed',
                    'message' => Lang::get('v1::messages.UserDosentExists'),
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;
            }

            /**
             * Get Category Data from helper
             */
            try {

                $responseData = $this->boughtLibrary(
                    $superStoreId,
                    $storeId,
                    $pageId,
                    $categoryProgramId,
                    $userCode,
                    $pageNumber,
                    $params
                );

                return $responseData;

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

        } elseif (strtolower($categoryProgramId) === 'rented_library') {
            if ($userCode === null) {
                $error = [
                    'code'    => 601,
                    'status'  => 'failed',
                    'message' => Lang::get('v1::messages.missingUserCode'),
                ];
                $this->logger->error(Utils::json($params), $error);

                return $error;
            }

            /**
             * Validate user Code
             */
            if (Validation::userExists($superStoreId, $userCode) === false) {
                $error = [
                    'code' => 604,
                    'status' => 'Failed',
                    'message' => Lang::get('v1::messages.UserDosentExists'),
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;
            }

            /**
             * Get Category Data from helper
             */
            try {

                $responseData = $this->rentedLibrary(
                    $superStoreId,
                    $storeId,
                    $pageId,
                    $categoryProgramId,
                    $userCode,
                    $pageNumber,
                    $params
                );

                return $responseData;

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
            'code'    => 400,
            'status'  => 'failed',
            'message' => Lang::get('v1::messages.invalidCategoryPID')
        ];
        $this->logger->error(Utils::json($params), $error);

        return $error;
    }

    /**
     * Helper Get Category Data (continue_watching)
     */
    protected function continueWatchig(
        $superStoreId,
        $storeId,
        $pageId,
        $categoryProgramId,
        $userCode,
        $pageNumber,
        $params
    )
    {
        $contentPerRequest = env('DASHBOARD_CONTENT_PER_PAGE', 20);

        $offset = ($pageNumber - 1) * $contentPerRequest;
        $nextPage = 0;

        $getContents = ContentPlaybackStatus::where([
            'user_code' => $userCode
        ])
        ->whereRaw('duration + 300 < total_duration')
        ->skip($offset)
        ->take($contentPerRequest)
        ->orderBy('updated_at', 'desc')
        ->get();

        if ($getContents->isEmpty()) {
            $error = [
                'code' => 400,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.NoData')
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        $getContentsCount = ContentPlaybackStatus::where([
            'user_code' => $userCode
        ])
        ->whereRaw('duration + 300 < total_duration')
        ->get()
        ->count();

        if ($getContentsCount > ($offset+$contentPerRequest)) {
            $nextPage = $pageNumber + 1;
        }

        $getContents = $getContents->toArray();

        foreach ($getContents as $content) {

            $tmp = [];

            $tmp['content_id'] = $content['content_id'];
            $tmp['content_type'] = $content['type'];

            $contentIds[] =  $tmp;
            unset($tmp);
        }

        if (isset($contentIds) && ! empty($contentIds)) {

            try {
                $platformParmas = $params;

                $platformParmas = [
                    'super_store_id'=> $superStoreId,
                    'contents' => $contentIds,
                    'locale' => $params['locale']
                ];

                $platformResponseContent = $this->callPublicamPlatform(
                    $superStoreId,
                    config('v1.platformApi.GetThubUrls'),
                    //Utils::getPlatformDetail('PlatformAPI')['GetThubUrls'],
                    $platformParmas,
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
                'message' => ErrorMessageConstants::ERROR_MESSAGES[$language]['somethingWrong'] ?? ErrorMessageConstants::ERROR_MESSAGES[$this->defaultMessageLanugage]['somethingWrong'],
                'error_message' => $exception->getMessage()
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;
            }

        }

        Utils::printData($platformResponseContent);
        exit();

        $error = [
            'code'    => 400,
            'status'  => 'failed',
            'message' => Lang::get('v1::messages.NoData')
        ];

        $this->logger->error(Utils::json($params), $error);
        return $error;
    }

    /**
     * Helper Get Category Data (recommended_movies)
     */
    protected function recommended(
        $superStoreId,
        $storeId,
        $pageId,
        $categoryProgramId,
        $pageNumber,
        $params
    )
    {
        $error = [
            'code'    => 400,
            'status'  => 'failed',
            'message' => Lang::get('v1::messages.NoData')
        ];

        $this->logger->error(Utils::json($params), $error);
        return $error;
    }

    /**
     * Helper Get Category Data (bought_library)
     */
    protected function boughtLibrary(
        $superStoreId,
        $storeId,
        $pageId,
        $categoryProgramId,
        $userCode,
        $pageNumber,
        $params
    )
    {
        $error = [
            'code'    => 400,
            'status'  => 'failed',
            'message' => Lang::get('v1::messages.NoData')
        ];

        $this->logger->error(Utils::json($params), $error);
        return $error;
    }

    /**
     * Helper Get Category Data (rented_library)
     */
    protected function rentedLibrary(
        $superStoreId,
        $storeId,
        $pageId,
        $categoryProgramId,
        $userCode,
        $pageNumber,
        $params
    )
    {
        $error = [
            'code'    => 400,
            'status'  => 'failed',
            'message' => Lang::get('v1::messages.NoData')
        ];

        $this->logger->error(Utils::json($params), $error);
        return $error;
    }
}
