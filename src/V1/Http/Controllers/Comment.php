<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use Validator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redis;
use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;
use Jet\Publicam\JetEngage\V1\Http\Traits\FileTrait;
use Jet\Publicam\JetEngage\V1\Models\Sql\UserDefaultProfile;
use Jet\Publicam\JetEngage\V1\Models\Sql\ContentEngagementCount;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Validation;

class Comment extends BaseController
{
    use HomeTrait;
    use FileTrait;
    public function __construct()
    {
        parent::__construct('comment');
    }

    /**
     * Post Moderated Content with mqtt
     *
     * @param array $payload
     */
    public function postComment(Request $request)
    {
        $payload = $request->all();

        /**
         * Payload Decryption
         */

        $decryptedPayload  = $this->decryptPayload($payload);

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

        /**
         * Locale Validation
         */
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
            'topicId' => 'required',
            'userCode'  =>  'required',
            'HasAttachment' =>  'required',
            'contentType'   =>  'required',
            'event' => 'required',
            'IsModerated'   => 'required',
            'pageId'    =>  'required',
            'portletId' =>  'required',
        ];

        if ( isset($params['HasAttachment']) && ($params['HasAttachment'] + 0) === 1) {
            if (! isset($params['attachment_type']) || $params['attachment_type'] == "") {
                $rule['attachment_type'] =  'required';
            }

            if (! isset($payload['media']) || empty($payload['media'])) {
                $rule['media'] =  'required';
            }
        }

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

        /**
         * Check whether provided user code exists or not
         */
        if (Validation::userExists($params['superStoreId'] + 0,$params['userCode']) === false) {
            $error = [
                "code" => 601,
                "status" => "failed",
                "message" => Lang::get('v1::messages.invalidUserCode')
            ];
            $this->logger->error(Utils::json($params), $error);
            return $error;
        }

        /**
         * If is_moderated is 0, check comment has attachment or not
         * If has attachment is 0, connect this as a client to server socket and
         * send the message if connection is available.
         * Else has attachment is 1 and attachment type is image, connect this as a client
         * to server socket and send the message if connection is available
         * else insert the premium comment in partial ugc comments collection
         */

        try{

            $platformParms=[];

            // Pass User  Data to platform
            $getUserProfile = UserDefaultProfile::where([
                'super_store_id' => $params['superStoreId'],
                'user_code' => $params['userCode']
            ])->first();

            $userName = "";
            $userProfileImage = "";

            if ($getUserProfile !== null) {
                $userName = $getUserProfile->name ?? "";
                $userProfileImage = $getUserProfile->profile_pic_url ?? "";
            }

            $platformParms[] = ['name' => 'user_name',
                'contents' => $userName
            ];
            $platformParms[] = ['name' => 'user_image',
                'contents' => $userProfileImage
            ];

            $platformParms[]= [
                'name' => 'super_store_id',
                'contents' => $params['superStoreId']
            ];

            $platformParms[] = ['name' => 'store_id',
                'contents' => $params['storeId']
            ];
            $platformParms[] = ['name' => 'user_code',
                'contents' => $params['userCode']
            ];
            $platformParms[] = ['name' => 'content_type',
                'contents' => $params['contentType']
            ];
            $platformParms[] = ['name' => 'in_reply_to_content_id',
                'contents' => $params['topicId']
            ];
            if(isset($params['InReplyCommentId']) && !empty($params['InReplyCommentId'])){
                $platformParms[] = ['name' => 'in_reply_to_comment_id',
                'contents' => $params['InReplyCommentId']
                ];
            }

            $platformParms[] = ['name' => 'is_moderated',
                'contents' => $params['IsModerated']
            ];
            $platformParms[] = ['name' => 'page_id',
                'contents' => $params['pageId']
            ];
            $platformParms[] = ['name' => 'portlet_id',
                'contents' => $params['portletId']
            ];

            $platformParms[] = ['name' => 'has_attachment',
                'contents' => $params['HasAttachment']
            ];
            if(isset($params['attachment_type']) && !empty($params['attachment_type'])){
                $platformParms[] = ['name' => 'attachment_type',
                    'contents' => $params['attachment_type']
                ];
            }
            if(isset($params['MqttTopic']) && !empty($params['MqttTopic'])){
                $platformParms[] = ['name' => 'mqtt_topic',
                    'contents' => $params['MqttTopic']
                ];
            }
            $platformParms[] = ['name' => 'comment',
                'contents' => $params['comment']
            ];


            if (($params['HasAttachment'] + 0) === 1) {

                if (isset($params['attachment_type']) || $params['attachment_type'] !== "") {

                    try {

                        /**
                         * Upload To Local Folder
                         */
                        $uploadResponse = $this->uploadToLocalFolder(
                            $prefix = 'comment_picture',
                            $contentId = $params['topicId'],
                            $storeId = $params['superStoreId'],
                            $fileParams = $payload,
                            $fileElement = 'media'
                        );

                        $platformParms[] = ['name' => 'media',
                            'contents' => fopen($uploadResponse, 'r')
                        ];

                    } catch (\Throwable $exception) {

                        $error = [
                            'code' => 400,
                            'status' => 'failed',
                            'message' => Lang::get('v1::messages.failed'),
                            'error_message' => $exception->getMessage()
                        ];

                        $this->logger->error(Utils::json($params), $error);

                        return $error;
                    }
                }
            }

            $superStoreId   =   $params['superStoreId'];
            $platformResponse = $this->callPublicamPlatformMultipart(
                $superStoreId,
                config('v1.platformApi.PostComment'),
                $platformParms,
                $this->logger
            );

            if ($platformResponse['code'] !== 200) {
                $this->logger->error(Utils::json($params), $platformResponse);
                return $platformResponse;
            }

            $isContentExists = ContentEngagementCount::where([
            'content_id' => $params['topicId'],
            'content_type' => $params['contentType'],
            'super_store_id' => $params['superStoreId']
            ])->exists();

            if ($isContentExists === false) {

                $comment_count=0;
                if($params['IsModerated']+0==0){
                    if( strtolower($params['attachment_type'])=='video' && $params['HasAttachment']+0==1){
                        $comment_count=0;
                    }else{
                       $comment_count=1;
                    }
                }

                $contentLikeShareEngagement = new ContentEngagementCount;

                $contentLikeShareEngagement->super_store_id = $params['superStoreId'];
                $contentLikeShareEngagement->content_id = $params['topicId'];
                $contentLikeShareEngagement->content_type = $params['contentType'];
                $contentLikeShareEngagement->like_count = 0;
                $contentLikeShareEngagement->share_count = 0;
                $contentLikeShareEngagement->comment_count = $comment_count;
                $contentLikeShareEngagement->save();

            } else {

                if($params['IsModerated']+0==0){
                    $updateCountFlag=1;
                    if( strtolower($params['attachment_type'])=='video' && $params['HasAttachment']+0==1){
                        $updateCountFlag=0;
                    }
                    if($updateCountFlag){
                        ContentEngagementCount::where([
                        'content_id' => $params['topicId'],
                        'content_type' => $params['contentType'],
                        'super_store_id' => $params['superStoreId']
                        ])->increment('comment_count');
                    }
                }
            }

       }catch (\Throwable $exception){

            $error = [
                'code' => 400,
                'status' => 'Failed',
                'message' => Lang::get('v1::messages.somethingWrong'),
                'error_message' => $exception->getMessage()
            ];

            $this->logger->error(Utils::json($params), $error);

            return $error;
        }

        if ($platformResponse['code'] == 200) {
            $response = [
                'code'    => 200,
                'status'  => 'success',
                'message' => $platformResponse['message'] ?? '',
                'data'    => $platformResponse['result'] ?? ''
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

    /**
     * Get Content Comment
     * @api
     * @param array
     * @return void
     */
    public function comments(Request $request)
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

        /**
         * Locale Validation
         */
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
            'contentId' => 'required',
            'page'  =>  'required',
            'contentType'   =>  'required',
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
        
        if ($params['superStoreId'] + 0 !== env('SuperStoreId') + 0) {
            $error = [
                "code" => 601,
                "status" => "failed",
                "message" => Lang::get('v1::messages.invalidSSID')
            ];
            $this->logger->error(Utils::json($params), $error);
            return $error;
        }

        try{

            $platformParms=[];
            $platformParms['super_store_id']=$params['superStoreId'];
            $platformParms['store_id']=$params['storeId'];
            $platformParms['page_number']=$params['page'];
            $platformParms['content_id']=$params['contentId'];
            $platformParms['content_type']=$params['contentType'];
            $platformParms['page_id']=$params['pageId'];
            $platformParms['locale']=$params['locale'];

            $superStoreId    =   $params['superStoreId'];

            $platformResponse = $this->callPublicamPlatform(
                $superStoreId,
                config('v1.platformApi.GetCommentByContentId'),
                $platformParms,
                $this->logger
            );
           
        if ($platformResponse['code'] !== 200) {
            $this->logger->error(Utils::json($params), $platformResponse);
            return $platformResponse;
        }

        $storeData  =   $platformResponse;
        
        if ($storeData['code']===200) {
            if(isset($storeData['data']) && ! empty($storeData['data'])) {
                $usercodes = [];
                $postIds=[];
                $postIds[]=$params['contentId'];
                switch ($params['contentType']){
                    case 'feed':
                        $getEngagement = Utils::getFeedEngagement_1_0($params, $postIds);
                    break;
                    default :
                        $getEngagement = Utils::getContentEngagement($params, $postIds);
                        break;
                }
                if (isset($getEngagement[$params['contentId']])) {
                    $storeData['data']['comment_count']=$getEngagement[$params['contentId']]['comment_count'];
                }

                if(isset($storeData['data']['comments'])){
                    foreach ($storeData['data']['comments'] as &$Comment) {

                        $user = [
                            'user_code' =>'',
                            'name' => '',
                            'profile_pic_url' => ''
                        ];

                        if (!isset($Comment['_id']) || $Comment['_id'] == null || $Comment['_id'] == '') {
                            continue;
                        }
                        if (isset($Comment['creator_type']) && $Comment['creator_type'] == 'user') {
                            $usercodes[]=$Comment['created_by'];
                            $Comment['userProfile'] = $user;

                        }
                        if($Comment['reply_count']){
                            foreach ($Comment['replies'] as $k => &$reply) {
                                if (!isset($reply['_id']) || $reply['_id'] == null || $reply['_id'] == '') {
                                    continue;
                                }
                                if (isset($reply['creator_type']) && $reply['creator_type'] == 'user') {
                                    $usercodes[]=$reply['created_by'];
                                    $reply['userProfile'] = $user;
                                }
                            }
                        }
                    }
                }
            if(count($usercodes)){
                $userInfo = UserDefaultProfile::whereIn('user_code', $usercodes)->get()->groupBy('user_code')->toArray();
            }

            if(isset($userInfo) && count($userInfo)){
                foreach ($storeData['data']['comments'] as $key => &$value) {
                    if (isset($value['creator_type']) && $value['creator_type'] == 'user') {
                        if(array_key_exists($value['created_by'], $userInfo)){
                            $userName = 'Guest';
                            if (isset($userInfo[$value['created_by']][0]['name']) && $userInfo[$value['created_by']][0]['name'] != '') {
                                $userName = $userInfo[$value['created_by']][0]['name'];
                            } elseif (isset($userInfo[$value['created_by']][0]['mobile']) && $userInfo[$value['created_by']][0]['mobile'] != '') {
                                $userName = $userInfo[$value['created_by']][0]['mobile'];
                            }
                            $value['userProfile']['user_code']=$value['created_by'];
                            $value['userProfile']['name']=$userName;
                            $value['userProfile']['profile_pic_url']=$userInfo[$value['created_by']][0]['profile_pic_url']??'';
                    }

                    }
                    if($value['reply_count']){
                    foreach ($value['replies'] as $k => &$reply) {
                    if (isset($reply['creator_type']) && $reply['creator_type'] == 'user') {
                            if(array_key_exists($reply['created_by'], $userInfo)){
                                $userName = 'Guest';
                                if (isset($userInfo[$reply['created_by']][0]['name']) && $userInfo[$reply['created_by']][0]['name'] != '') {
                                        $userName = $userInfo[$reply['created_by']][0]['name'];
                                    } elseif (isset($userInfo[$reply['created_by']][0]['mobile']) && $userInfo[$reply['created_by']][0]['mobile'] != '') {
                                        $userName = $userInfo[$reply['created_by']][0]['mobile'];
                                    }
                                    $reply['userProfile']['user_code']=$reply['created_by'];
                                    $reply['userProfile']['name']=$userName;
                                    $reply['userProfile']['profile_pic_url']=$userInfo[$reply['created_by']][0]['profile_pic_url']??'';
                            }
                        }
                    }
                }
                $value['reply_count']=Utils::restyleText($value['reply_count'] + 0);
                }
            }
                $response = [
                    'code' => $storeData['code'],
                    'status' => $storeData['status'],
                    'message' => $storeData['message'] ?? '',
                    'data' => $storeData['data']
                ];

                $this->logger->info(Utils::json($params), $response);

                return $response;
            }
        }

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
    }

}
