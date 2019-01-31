<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use config;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Jet\Publicam\JetEngage\V1\Models\Sql\ContentRatings;
use Illuminate\Support\Facades\Redis;
use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Validation;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;

class Rating extends BaseController
{
    use HomeTrait;

    public function __construct()
    {
        parent::__construct('rating');
    }

    /**
     * Get Application Configuration
     * @return Response
     */
    public function post(Request $request)
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

        \App::setLocale($language);

        $messages = [
            'required' => Lang::get('v1::messages.required')
        ];

        $rule = [
            'superStoreId' => 'required',
            'storeId'   =>  'required',
            'pageId'    =>  'required',
            'portletId' =>  'required',
            'contentId' =>  'required',
            'packageId' =>  'required',
            'contentType'   =>  'required',
            'userCode'  =>  'required',
            'rating'    =>  'required',
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

        $platform = $params['locale']['platform'];

        $superStoreId = $params['superStoreId'];
        $seriesId   =   ( isset($params['seriesId']) && $params['seriesId'] != '') ? $params['seriesId'] : null;
        $seasonId   =   ( isset($params['seasonId']) && $params['seasonId'] != '') ? $params['seasonId'] : null;
        $userCode   =   $params['userCode'];
        /**
         * Validate user Code
         */
        if (Validation::userExists($superStoreId, $userCode) === false) {
            $error = [
                'code' => 604,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.UserDosentExists')
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        try {

            $ratingData  =   ContentRatings::where([
                'user_code'=>$params['userCode'],
                'content_id'=>$params['contentId'],
                'package_id'=>$params['packageId']
                ])->get()->toArray();

            if ($ratingData ==  null) {

                $newRating  =   new ContentRatings;

                $newRating->super_store_id  =   $params['superStoreId'];
                $newRating->store_id  =   $params['storeId'];
                $newRating->page_id  =   $params['pageId'];
                $newRating->portlet_id  =   $params['portletId'];
                $newRating->package_id  =   $params['packageId'];
                $newRating->series_id  =   $seriesId;
                $newRating->season_id  =   $seasonId;
                $newRating->content_type  =   $params['contentType'];
                $newRating->user_code  =   $params['userCode'];
                $newRating->content_id  =   $params['contentId'];
                $newRating->rating  =   $params['rating'];

                if($newRating->save()){
                    $response = [
                        'code' => 200,
                        'status' => 'success',
                        'message' => Lang::get('v1::messages.success')
                    ];

                    $this->logger->info(Utils::json($params), $response);

                    return $response;
                }

                $error = [
                    'code' => 400,
                    'status' => 'Failed',
                    'message' => Lang::get('v1::messages.somethingWrong'),
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;

            }else{
                $error = [
                    'code' => 400,
                    'status' => 'Failed',
                    'message' => Lang::get('v1::messages.alreadyRated'),
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;
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

        $language = strtoupper($params['locale']['language'] ?? '');

        \App::setLocale($language);

        $messages = [
            'required' => Lang::get('v1::messages.required')
        ];

        $rule = [
            'superStoreId' => 'required',
            'storeId' => 'required',
            'locale' => 'required',
            'contentId' =>  'required',
            'packageId' =>  'required',
            'pageId'    =>  'required',
            'portletId' =>  'required',
            'contentType'   =>  'required'
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

        try {
            $platformParams = [
                'super_store_id' => $superStoreId,
                'store_id' => $params['storeId'],
                'page_id' => $params['pageId'],
                'portlet_id' => $params['portletId'],
                'package_id' => $params['packageId'],
                'content_id' => $params['contentId'],
                'content_type' => $params['contentType'],
                'locale' => $params['locale']
            ];

            $platformResponse = $this->callPublicamPlatform(
                $superStoreId,
                config('v1.platformApi.ContentDetails'),
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

        if(isset($platformResponse['data']) && !empty($platformResponse['data'])) {

            //print_r($platformResponse['data']);
            $ratings = ContentRatings::where([
                'super_store_id'    =>  $superStoreId,
                'store_id'  => $params['storeId'],
                'content_id'    => $params['contentId']
            ])->get();

            $userCount  =   count($ratings->toArray());

            $platformRating =  4;
            
            $totalRatings = ContentRatings::where([
                'super_store_id'    =>  $superStoreId,
                'store_id'  => $params['storeId'],
                'content_id'    => $params['contentId']
            ])->sum('rating');

            $platformRating = $platformRating * intval(env('AVERAGE_USER_COUNT'));
            
            $totalUserCount =   intval(env('AVERAGE_USER_COUNT')) + $userCount;       
            
            $contentRating  = ($platformRating + $totalRatings) / $totalUserCount;

            $response = [
                'code' => 200,
                'status' => 'success',
                'message' => 'success',
                'data' => $contentRating
            ];

            $this->logger->info(Utils::json($params), $response);

            return $response;
        }

        $error = [
            'code' => 400,
            'status' => 'failed',
            'message' => Lang::get('v1::messages.NoData')
        ];

        $this->logger->error(Utils::json($params), $error);

        return $error;

    }
}
