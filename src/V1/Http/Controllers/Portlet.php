<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use config;
use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use App\Models\Sql\Configuration;
use Illuminate\Support\Facades\Redis;
use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;
//use Jet\Publicam\JetEngage\V1\Http\Traits\PurchaseTrait;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Validation;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;

/**
 * Portlet Class
 *
 * @library         JetEngage
 *
 * @license         <add Licence here>
 * @link            www.jetsynthesys.com
 * @author          Riyaz Patwegar <riyaz.patwegar@jetsynthesys.com>
 * @since           Jan 23, 2019
 * @copyright       2019 Jetsynthesys Pvt Ltd.
 * @version         1.0
 */
class Portlet extends BaseController
{
    use HomeTrait;
    //use PurchaseTrait;
    public function __construct()
    {
        parent::__construct('portlet');
    }

    /**
     * Retrieve Store Listing Data with locale
     *
     * @api
     * @version 1.0
     * @param array $params
     * @return array
     */

    public function getPagePortlets(Request $request)
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

        try{
                $platformParams['super_store_id']=$params['superStoreId'];
                $platformParams['store_id']=$params['storeId'];
                $platformParams['page_id']=$params['pageId'];
                $platformParams['locale']=$params['locale'];
                $superStoreId   =   $params['superStoreId'];

                $platformResponse = $this->callPublicamPlatform(
                    $superStoreId,
                    config('v1.platformApi.Portletlist'),
                    $platformParams,
                    $this->logger
                );

                if ($platformResponse['code'] !== 200) {
                    $this->logger->error(Utils::json($params), $platformResponse);
                    return $platformResponse;
                }

                $storeData = $platformResponse;

                if ($storeData['code']===200) {
                if(isset($storeData['data']) && ! empty($storeData['data'])) {
                if(count($storeData['data']['portlet_data']))
                    {
                        $postIds = [];
                        foreach ($storeData['data']['portlet_data'] as &$record) {
                                $record['is_liked'] = 0;
                                $postIds[] = $record['portlet_id'];
                                /**
                                 * When entities array is blank we removed it from array.
                                */
                                if(isset($record['entities']) && is_array($record['entities']) &&!count($record['entities'])){
                                    unset($record['entities']);
                                }

                                $record['engagement'] = [
                                    'is_liked' => 0,
                                    'is_viewed' => 0,
                                    'like_count' => "0",
                                    'share_count' => "0",
                                    'comment_count' => "0",
                                    'view_count' => "0",
                                    'reply_count' => "0"
                                ];
                            }
                            $params['content_type']='portlet';
                            switch ($params['content_type']){
                                case 'feed':
                                    $getEngagement = Utils::getFeedEngagement($params, $postIds);
                                break;
                                default :
                                    $getEngagement = Utils::getContentEngagement($params, $postIds);
                                    break;
                            }

                        foreach ($storeData['data']['portlet_data'] as &$record) {
                            if (isset($getEngagement[$record['portlet_id']])) {
                                $record['likes'] = $getEngagement[$record['portlet_id']]['like_count'];
                                $record['shares'] = $getEngagement[$record['portlet_id']]['share_count'];
                                $record['comments'] = $getEngagement[$record['portlet_id']]['comment_count'];
                                $record['views'] = $getEngagement[$record['portlet_id']]['view_count'];
                                $record['is_liked'] = $getEngagement[$record['portlet_id']]['is_liked'];

                                $record['engagement']['like_count'] = $getEngagement[$record['portlet_id']]['like_count'];
                                $record['engagement']['share_count'] = $getEngagement[$record['portlet_id']]['share_count'];
                                $record['engagement']['comment_count'] = $getEngagement[$record['portlet_id']]['comment_count'];
                                $record['engagement']['view_count'] = $getEngagement[$record['portlet_id']]['view_count'];
                                $record['engagement']['is_liked'] = $getEngagement[$record['portlet_id']]['is_liked'];
                                $record['engagement']['reply_count'] =$getEngagement[$record['id']]['reply_count'];
                            }
                        }
                    }

                    $response = [
                        'code' => 200,
                        'status' => 'success',
                        'message' => $storeData['message'],
                        'data' => $storeData['data']
                    ];

                    $this->logger->info(Utils::json($params), $response);

                    return $response;
                }
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
     * Retrieve Portlet details  with locale
     *
     * @api
     * @version 1.0
     * @param array $params
     * @return array
     */

    public function getPortletDetails(Request $request)
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
            'page'  =>  'required',
            'portletId' => 'required'
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

        $userCode = (isset($params['userCode']) && $params['userCode'] != '') ? $params['userCode'] : null;

        /**
         * If userCode is not null, validate userCode
         */
        if ($userCode !== null) {
            if (Validation::userExists($params['superStoreId'],$userCode) === false) {
                $error = [
                    "code" => 601,
                    "status" => "Failed",
                    "message" => Lang::get('v1::messages.invalidUserCode')
                ];

                $this->logger->error(Utils::json($params), $error);

                return $error;
            }
        }

        try{
            $platformParams['super_store_id']=$params['superStoreId'];
            $platformParams['store_id']=$params['storeId'];
            $platformParams['page_id']=$params['pageId'];
            $platformParams['portlet_id']=$params['portletId'];
            $platformParams['page']=$params['page'];
            $platformParams['locale']=$params['locale'];
            $superStoreId   = $params['superStoreId'];
            unset($platformParams['superStoreId'], $platformParams['storeId'], $platformParams['pageId'],$platformParams['portletId']);

            $platformResponse = $this->callPublicamPlatform(
                $superStoreId,
                config('v1.platformApi.PortletContentList'),
                $platformParams,
                $this->logger
            );

            if ($platformResponse['code'] !== 200) {
                $this->logger->error(Utils::json($params), $platformResponse);
                return $platformResponse;
            }

            //if (isset($platformResponse['data']) && !empty($platformResponse['data'])) {
            /**
                 * @Todo: If userCode is not null,
                 * Identify Content is purchased or not
                 */

                // if ($userCode !== null) {
                //     foreach ($platformResponse['data']['content_data'] as &$content) {
                //         $isPurchased = $this->isContentPurchasedAndValidForPlayback(
                //             $userCode,
                //             $platformResponse['data']['package_information']['package_id'],
                //             $content['id']
                //         );
                //         // need to check multiplan here
                //         //if(isset($content['charging_info'])){
                //             $content['is_purchased'] = $isPurchased === true ? 1 : 0;
                //         //}
                //     }
                // }
            //}

        } catch (\Throwable $exception){

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
