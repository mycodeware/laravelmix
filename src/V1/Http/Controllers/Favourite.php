<?php
namespace Jet\Publicam\JetEngage\V1\Http\Controllers;

use Validator;
use config;
use Jet\Publicam\JetEngage\V1\Http\Traits\HomeTrait;
use App\Models\Sql\Configuration;
use App\User;
use Illuminate\Support\Facades\Lang;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Jet\Publicam\JetEngage\V1\Models\Sql\UserFavouriteContent;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;

//use Jet\Publicam\Api\JetEngage\Models\Sql\UserFavouriteContent;

/**
 * Favourite Class
 *
 * @library         JetEngage
 *
 * @license         <add Licence here>
 * @link            www.jetsynthesys.com
 * @author          Imran Khan <imran.khan@jetsynthesys.com>
 * @since           Dec 02, 2018
 * @copyright       2018 Jetsynthesys Pvt Ltd.
 * @version         1.0
 */
class Favourite extends BaseController
{
 use HomeTrait;
   

    public function __construct()
    {
       
        parent::__construct('favourite');

      
    }

  
    public function addContent(Request $request)
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
                                    "status" => Lang::get('v1::messages.failed'),
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
                              'storeId' => 'required', 
                              'pageId' => 'required', 
                              'portletId' => 'required', 
                              'packageId' => 'required', 
                              'seriesId' => 'nullable|integer', 
                              'seasonId' => 'nullable|integer', 
                              'contentId' => 'required', 
                              'contentType' => 'required', 
                              'userCode' => 'required'

                            ];
                            $validator = Validator::make($params, $rule, $messages);
                            if ($validator->fails()) {
                                $errors = $validator->errors();

                                foreach ($errors->all() as $error) {
                                    $errorResponse = [
                                        'code' => 601,
                                        'status' => Lang::get('v1::messages.failed'),
                                        'message' => $error
                                    ];
                                    $this->logger->error(Utils::json($params), $errorResponse);
                                    return $errorResponse;
                                }
                            }




                                    $user = User::where('super_store_id', '=',$params['superStoreId'])->where('user_code','=',$params['userCode'])->first();



                                            //Check If user exist
                                             if ($user === NULL) {

                                               
                                                $error = [
                                                    'code' => 604,
                                                    'status' => Lang::get('v1::messages.failed'),
                                                    'message' => Lang::get('v1::messages.UserDosentExists')
                                                ];

                                                $this->logger->error(Utils::json($params), $error);

                                                return $error;
                                            }


                                                try {
                                            
                                            //check if already favourate
                                                $contentEngagement = UserFavouriteContent::where([
                                                    'user_code' => $params['userCode'],
                                                    'super_store_id' => $params['superStoreId'],
                                                    'content_id' => $params['contentId'],
                                                    //'is_active' => 1
                                                ])->first();

           

                                              if ($contentEngagement !== null) {

                                                if($contentEngagement->is_active == 1) {

                                                    $error =  [
                                                        'code' => '400',
                                                        'status' => Lang::get('v1::messages.failed'),
                                                        'message' => Lang::get('v1::messages.alreadyFavourite')
                                                    ];
                                                    
                                                    $this->logger->error(Utils::json($params), $error);
                                                    
                                                    return $error;
                                                
                                                }
                                                else {
                                                  
                                                    $contentEngagement->is_active = 1;
                                                    
                                                    $contentEngagement->save();
                                                    
                                                    $response =  [
                                                        'code' => 200,
                                                        'status' =>  Lang::get('v1::messages.success'),
                                                        'message' => Lang::get('v1::messages.addedToFavourite')
                                                    ];
                                                    
                                                    $this->logger->info(Utils::json($params), $response);
                                                    
                                                    return $response;
                                                }
                                                //die();



                                }

                                    $contentEngagement = new UserFavouriteContent;
                                    $contentEngagement->user_code = $params['userCode'];
                                    $contentEngagement->content_id = $params['contentId'];
                                    $contentEngagement->super_store_id = $params['superStoreId'];
                                    $contentEngagement->store_id = $params['storeId'];
                                    $contentEngagement->page_id = $params['pageId'];
                                    $contentEngagement->portlet_id = $params['portletId'];
                                    $contentEngagement->package_id = $params['packageId'];
                                    $contentEngagement->type = strtolower($params['contentType']);
                            
                            if(isset($params['seriesId']) && $params['seriesId'] > 0) {
                                $contentEngagement->series_id = $params['seriesId'];
                                $contentEngagement->season_id = $params['seriesId'];
                            }
                            
                            $contentEngagement->save();
                            
                            $response =  [
                                'code' => 200,
                                'status' => Lang::get('v1::messages.success'),
                                'message' =>Lang::get('v1::messages.addedToFavourite')
                            ];
                            
                            $this->logger->info(Utils::json($params), $response);
                            
                            return $response;

                             } catch (\Throwable $exception) {
                                
                                echo $exception;
                                    $error = [
                                        'code' => 400,
                                        'status' => Lang::get('v1::messages.failed'),
                                        'message' => Lang::get('v1::messages.somethingWrong')
                                    ];
                                    
                                    $this->logger->error(Utils::json($params), $error);
                                    
                                    return $error;
                                }
    }

   public function removeContent(Request $request)
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
                                            "status" => Lang::get('v1::messages.failed'),
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
                                    
                                      'contentId' => 'required', 
                                      'contentType' => 'required', 
                                      'userCode' => 'required'

                                    ];
                                    $validator = Validator::make($params, $rule, $messages);
                                    if ($validator->fails()) {
                                        $errors = $validator->errors();

                                        foreach ($errors->all() as $error) {
                                            $errorResponse = [
                                                'code' => 601,
                                                'status' => Lang::get('v1::messages.failed'),
                                                'message' => $error
                                            ];
                                            $this->logger->error(Utils::json($params), $errorResponse);
                                            return $errorResponse;
                                        }
                                    }




                                    $user = User::where('super_store_id', '=',$params['superStoreId'])->where('user_code','=',$params['userCode'])->first();



                                //Check If user exist
                                 if ($user === NULL) {

                                   
                                    $error = [
                                        'code' => 604,
                                        'status' => Lang::get('v1::messages.failed'),
                                        'message' => Lang::get('v1::messages.UserDosentExists')
                                    ];

                                    $this->logger->error(Utils::json($params), $error);

                                    return $error;
                                }
                                try {
        
                                    $contentEngagement = UserFavouriteContent::where([
                                        'user_code' => $params['userCode'],
                                        'super_store_id' => $params['superStoreId'],
                                        'content_id' => $params['contentId'],
                                        'type' => strtolower($params['contentType'])
                                        //'is_active' => 1
                                    ])->first();

                                    if ($contentEngagement !== null) {
                                        if($contentEngagement->is_active !== 1) {
                                            $error =  [
                                                'code' => 400,
                                                'status' =>Lang::get('v1::messages.failed'),
                                                'message' => Lang::get('v1::messages.alreadyRemovedFavourite')
                                            ];
                                            
                                            $this->logger->error(Utils::json($params), $error);
                                            
                                            return $error;
                                        
                                        } else {
                                        
                                            $contentEngagement->is_active = 0;
                                            
                                            $contentEngagement->save();
                                            
                                            $response =  [
                                                'code' => 200,
                                                'status' => 'success',
                                                'message' =>Lang::get('v1::messages.removedFromFavourite')
                                            ];
                                            
                                            $this->logger->info(Utils::json($params), $response);
                                            
                                            return $response;
                                        }
                                    }
                                    
                                    $error =  [
                                        'code' => 400,
                                        'status' => Lang::get('v1::messages.failed'),
                                        'message' => Lang::get('v1::messages.notInFavouriteList')
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

      public function getIds(Request $request)
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
                            "status" => Lang::get('v1::messages.failed'),
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
                        'userCode' => 'required'

                    ];
                    $validator = Validator::make($params, $rule, $messages);
                    if ($validator->fails()) {
                        $errors = $validator->errors();

                        foreach ($errors->all() as $error) {
                            $errorResponse = [
                                'code' => 601,
                                'status' => Lang::get('v1::messages.failed'),
                                'message' => $error
                            ];
                            $this->logger->error(Utils::json($params), $errorResponse);
                            return $errorResponse;
                        }
                    }


                                                $user = User::where('super_store_id', '=',$params['superStoreId'])->where('user_code','=',$params['userCode'])->first();


                                            //Check If user exist
                                             if ($user === NULL) {

                                               
                                                $error = [
                                                    'code' => 604,
                                                    'status' => Lang::get('v1::messages.failed'),
                                                    'message' => Lang::get('v1::messages.UserDosentExists')
                                                ];

                                                $this->logger->error(Utils::json($params), $error);

                                                return $error;
                                            }


                                              try {
                    
                        $contentEngagement = UserFavouriteContent::where([
                            'user_code' => $params['userCode'],
                            'super_store_id' => $params['superStoreId'],
                            'is_active' => 1
                        ])
                        ->select('content_id')
                        ->get();
                        
                        if (! $contentEngagement->isEmpty()) {
                            
                            $contentEngagement =  $contentEngagement->toArray();
                            
                            foreach($contentEngagement as $content) {
                                $contentIds[] = (string) $content['content_id'];
                            }
                            
                            if(isset($contentIds) && !empty($contentIds)) {
                                $response =  [
                                    'code' => 200,
                                    'status' => 'success',
                                    'message' => Lang::get('v1::messages.success'),
                                    'data' => [
                                        'content_ids' => $contentIds,
                                    ],
                                ];
                                
                                $this->logger->info(Utils::json($params), $response);
                                
                                return $response;
                            }
                        }
                        
                        $error =  [
                            'code' => 400,
                            'status' => 'failed',
                            'message' => Lang::get('v1::messages.NoData')
                        ];
                        
                        $this->logger->error(Utils::json($params), $error);
                        
                        return $error;
                    
                    } catch (\Throwable $exception) {
                    

                    echo $exception->getMessage();
                        $error = [
                            'code' => 400,
                            'status' => 'Failed',
                            'message' =>Lang::get('v1::messages.somethingWrong')
                        ];
                        
                        $this->logger->error(Utils::json($params), $error);
                        
                        return $error;
        }



    }

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
                            "status" => Lang::get('v1::messages.failed'),
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
                        'userCode' => 'required',
                        'pageNumber'=>'required|integer'

                    ];
                    $validator = Validator::make($params, $rule, $messages);
                    if ($validator->fails()) {
                        $errors = $validator->errors();

                        foreach ($errors->all() as $error) {
                            $errorResponse = [
                                'code' => 601,
                                'status' => Lang::get('v1::messages.failed'),
                                'message' => $error
                            ];
                            $this->logger->error(Utils::json($params), $errorResponse);
                            return $errorResponse;
                        }
                    }


                                                $user = User::where('super_store_id', '=',$params['superStoreId'])->where('user_code','=',$params['userCode'])->first();


                                            //Check If user exist
                                             if ($user === NULL) {

                                               
                                                $error = [
                                                    'code' => 604,
                                                    'status' => Lang::get('v1::messages.failed'),
                                                    'message' => Lang::get('v1::messages.UserDosentExists')
                                                ];

                                                $this->logger->error(Utils::json($params), $error);

                                                return $error;
                                            }

                                             $contentPerRequest = getenv('FAVOURITE_CONTENT_PER_PAGE');
        
                                        $offset = ($params['pageNumber'] - 1) * $contentPerRequest;
                                        $nextPage = 0;


                                                                                 try {
                                        
                                            $contentEngagement = UserFavouriteContent::where([
                                                'user_code' => $params['userCode'],
                                                'super_store_id' => $params['superStoreId'],
                                                'is_active' => 1
                                            ])
                                            ->skip($offset)
                                            ->take($contentPerRequest)
                                            ->orderBy('updated_at', 'desc')
                                            ->get();
                                            
                                            $getContentsCount = UserFavouriteContent::where([
                                                'user_code' => $params['userCode'],
                                                'super_store_id' => $params['superStoreId'],
                                                'is_active' => 1
                                            ])
                                            ->count();
                                            
                                            if ($getContentsCount > ($offset+$contentPerRequest)) {
                                                $nextPage = $params['pageNumber'] + 1;
                                            }
                                            
                                            if (! $contentEngagement->isEmpty()) {
                                                
                                                $contentEngagement = $contentEngagement->toArray();
                                               
                                                foreach($contentEngagement as $content) {
                                                    $tmp = [];
                                                    
                                                    $tmp['content_id'] = $content['content_id'];
                                                    $tmp['content_type'] = $content['type'];
                                                    
                                                    $contentIds[] =  $tmp;
                                                    unset($tmp);
                                                    
                                                }
                                                
                                                if(isset($contentIds) && !empty($contentIds)) {
                                                    
                                                    try {
                                                        $platformParmas = $params;
                                                        
                                                        $platformParmas['super_store_id'] = $params['superStoreId'];
                                                        
                                                        $platformParmas['contents'] = $contentIds;
                                                        
                                                        unset($platformParmas['superStoreId'], $platformParmas['storeId'], $platformParmas['pageId']);
                                                        
                                                        //$httpClient = $this->getService('httpClient');
                                                        //$redisClient = $this->getService('redisClient');
                                                        
                                                        $platformResponseContent = $this->callPublicamPlatform(
                                                            $params['superStoreId'],
                                                            //Utils::getPlatformDetail('PlatformAPI')['GetThubUrls'],
                                                            config('v1.platformApi.GetThubUrls'),
                                                            $platformParmas,
                                                            $this->logger
                                                            //$httpClient,
                                                            //$redisClient
                                                            );
                                                            
                                                            if ($platformResponseContent['code'] !== 200) {
                                                                $this->logger->error(Utils::json($params), $platformResponseContent);
                                                                return $platformResponseContent;
                                                            }
                                                            
                                                    } catch (\Throwable $exception) {
                                                        $error = [
                                                            'code' => 400,
                                                            'status' => 'Failed',
                                                            'message' =>Lang::get('v1::messages.somethingWrong'),
                                                            'error_message' => $exception->getMessage()
                                                        ];
                                                        
                                                        $this->logger->error(Utils::json($params), $error);
                                                        
                                                        return $error;
                                                    }
                                                }
                                                
                                                $resultData = [];
                                                
                                                if (
                                                    isset($platformResponseContent['data']) &&
                                                    !empty($platformResponseContent['data'])
                                                ) {
                                                
                                                    foreach ($contentEngagement as $content) {
                                                        
                                                        foreach ($platformResponseContent['data'] as $thumb) {
                                                            
                                                            if ($thumb['content_id'] == $content['content_id']) {
                                                                $tmp = [];
                                                                
                                                                $tmp['content_id'] = "".$content['content_id']."";                                        $tmp['super_store_id'] = $content['super_store_id'];
                                                                $tmp['store_id'] = $content['store_id'];
                                                                $tmp['page_id'] = $content['page_id'];
                                                                $tmp['portlet_id'] = $content['portlet_id'];
                                                                $tmp['package_id'] = $content['package_id'];
                                                                $tmp['content_type'] = $content['type'];
                                                                $tmp['thumb_url'] = $thumb['thumb_image'];
                                                                $tmp['updated_at'] = $content['updated_at'];
                                                                
                                                                if (isset($content['series_id']) && $content['series_id'] !== 0) {
                                                                    $tmp['series_id'] = $content['series_id'];
                                                                }
                                                                
                                                                if (isset($content['season_id']) && $content['season_id'] !== 0) {
                                                                    $tmp['season_id'] = $content['season_id'];
                                                                }
                                                                
                                                                $resultData[] = $tmp;
                                                                unset($tmp);
                                                            }
                                                        }
                                                    }
                                                }
                                                
                                                if (isset($resultData) && ! empty($resultData)) {
                                                
                                                    $response = [
                                                        'code' => 200,
                                                        'status' => 'success',
                                                        'message' => Lang::get('v1::messages.success'),
                                                        'data' => [
                                                            'content' => $resultData
                                                        ]
                                                    ];
                                                    
                                                    if (isset($nextPage) && $nextPage !== 0) {
                                                        $response['data']['next'] = $nextPage;
                                                    }
                                                    
                                                    $this->logger->info(Utils::json($params), $response);
                                                    
                                                    return $response;
                                                }
                                                
                                            }
                                            
                                            $error =  [
                                                'code' => 400,
                                                'status' => 'failed',
                                                'message' => Lang::get('v1::messages.NoData'),
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
