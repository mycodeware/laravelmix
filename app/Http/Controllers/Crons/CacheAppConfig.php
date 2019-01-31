<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers\Crons;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Sql\Configuration;
use Illuminate\Support\Facades\Redis;
use App\Models\Sql\StoreLoginSocialPlatformMapping;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Http\Controllers\BaseController;

class CacheAppConfig extends BaseController
{
    protected $platforms = ['android', 'ios', 'windows'];

    public function __construct()
    {
        parent::__construct('cache_app_config');
    }

    /**
     * Get Application Configuration
     * @return Response
     */
    public function index(Request $request)
    {
        $packageName = env('APP_PACKAGE_NAME', '');

        $configurations = Configuration::where('package_name', $packageName)
            ->orderBy('sequence_id', 'asc')
            ->get()
            ->toArray();

            if (!empty($configurations)) {

                foreach ($this->platforms as $platform) {

                    $getConfig = Redis::get($packageName.'-'.$platform);

                    if ($getConfig !== null) {

                        $result = Redis::del($packageName.'-'.$platform);

                        $this->logger->info("Deleted : $packageName-$platform");

                        Utils::printData("Deleted : $packageName-$platform");

                    }

                    $superStoreId = 0;
                    $feedback = $splashAd = [];

                    // Separate Configration data via keyword
                    foreach ($configurations as $configuration) {

                        if ($configuration['keyword'] === 'SUPER_STORE') {
                            $superStoreId = (int)$configuration['value_data'];

                        } elseif ($configuration['keyword'] === 'FEEDBACK') {

                            $temp['id'] = $configuration['id'];
                            $temp['topic'] = $configuration['value_data'];
                            $feedback[] = $temp;
                            unset($temp);

                        } elseif ($configuration['keyword'] === 'POWERED_BY') {

                            if (
                                isset($configuration['value_data']) &&
                                $configuration['value_data'] !== "" &&
                                $configuration['value_data'] !== null
                            ) {
                                $splashAd[$configuration['display_data']] = $configuration['value_data'];
                            }
                        } elseif ($configuration['keyword'] === 'APP_SHARE_URL') {
                            $appShareUrl = $configuration['value_data'];
                        } elseif ($configuration['keyword'] === 'APP_VERSION') {

                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (
                                    isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    $versionData[$configuration['display_data']] = $configuration['value_data'];
                                }
                            }
                        } elseif ($configuration['keyword'] === 'ANALYTICS') {

                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (
                                    isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    $analytics_detail[$configuration['display_data']] = $configuration['value_data'];
                                }
                            }
                        } elseif ($configuration['keyword'] === 'URLS') {

                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (
                                    isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    $helpUrls[$configuration['display_data']] = $configuration['value_data'];
                                }
                            }
                        } elseif ($configuration['keyword'] === 'BLOCK_APP') {

                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (
                                    isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    $blockApps[] = $configuration['value_data'];
                                }
                            }
                        } elseif ($configuration['keyword'] === 'SUPPORT_EMAIL') {

                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (
                                    isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    $supportEmail = $configuration['value_data'];
                                }
                            }
                        } elseif ($configuration['keyword'] === 'TXN_ERROR_MSG') {

                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (
                                    isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    $txnErrorMsg = $configuration['value_data'];
                                }
                            }
                        } elseif ($configuration['keyword'] === 'EXTERNAL_URL') {

                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (
                                    isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    $externalUrl[$configuration['display_data']] = $configuration['value_data'];
                                }
                            }
                        } elseif ($configuration['keyword'] === 'APP_SUBSCRIPTION_PKG_ID') {
                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    $appSubscriptionPkgIdKey = $configuration['display_data'];
                                    $appSubscriptionPkgId = $configuration['value_data'];
                                }
                            }
                        } elseif ($configuration['keyword'] === 'MESSAGES') {
                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    $messages[$configuration['display_data']] = $configuration['value_data'];
                                }
                            }
                        } elseif ($configuration['keyword'] === 'MGR_STORE') {
                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    $mgrStore[$configuration['display_data']] = $configuration['value_data'];
                                }
                            }
                        } elseif ($configuration['keyword'] === 'MAINTENANCE_SETTINGS') {
                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    if($configuration['display_data'] == 'MaintenanceFlag') {
                                        $maintenanceSettings[$configuration['display_data']] = $configuration['value_data']+0;
                                    } else {
                                        $maintenanceSettings[$configuration['display_data']] = $configuration['value_data'];
                                    }
                                }
                            }
                        }elseif($configuration['keyword'] === 'MENU_GROUP_ID'){
                                    $getPlatforms = explode(",", $configuration['platform']);
                                    if (in_array($platform, $getPlatforms)) {
                                      if (isset($configuration['value_data']) &&
                                            $configuration['value_data'] !== "" &&
                                            $configuration['value_data'] !== null
                                        ) {
                                          $menuSettings[$configuration['display_data']] = $configuration['value_data'];
                                        }
                                    }
                        } elseif ($configuration['keyword'] === 'APP_SHARE_TEXT') {
                            $appShareText = $configuration['value_data'];
                        } elseif ($configuration['keyword'] === 'PLAYER_CONFIGURATION') {
                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    if($configuration['display_data'] == 'MaintenanceFlag') {
                                        $playerConfiguration[$configuration['display_data']] = $configuration['value_data']+0;
                                    } else {
                                        $playerConfiguration[$configuration['display_data']] = $configuration['value_data'];
                                    }
                                }
                            }
                        } elseif ($configuration['keyword'] === 'CUSTOM_CONFIGURATION') {
                            $getPlatforms = explode(",", $configuration['platform']);

                            if (in_array($platform, $getPlatforms)) {
                                if (isset($configuration['value_data']) &&
                                    $configuration['value_data'] !== "" &&
                                    $configuration['value_data'] !== null
                                ) {
                                    if($configuration['display_data'] == 'MaintenanceFlag') {
                                        $customConfiguration[$configuration['display_data']] = $configuration['value_data']+0;
                                    } else {
                                        $customConfiguration[$configuration['display_data']] = $configuration['value_data'];
                                    }
                                }
                            }
                        }
                    }

                    $loginTypeLists = StoreLoginSocialPlatformMapping::where([
                        'store_id' => $superStoreId,
                        'type' => 'login'
                    ])->with('platform')->orderBy('sequence_id', 'asc')->get()->toArray();

                    $loginTypeList = [];

                    foreach ($loginTypeLists as $list) {
                        $tmp['id'] = $list['platform']['id'];
                        $tmp['type'] = $list['platform']['name'];
                        $loginTypeList[] = $tmp;
                        unset($tmp);
                    }

                    $facebookApp = [
                        'AppId' => env('FACEBOOK_APP_ID', ''),
                        'Secret' => env('FACEBOOK_APP_SECRET', ''),
                    ];

                    $response = [
                        'code' => 200,
                        'status' => 'success',
                        'message' => 'success',
                        'data' => [
                            'superStoreId' => $superStoreId,
                        ]
                    ];

                    if (isset($appSubscriptionPkgId) && $appSubscriptionPkgId != "") {
                        $response['data'][$appSubscriptionPkgIdKey] = $appSubscriptionPkgId;
                    }

                    unset($appSubscriptionPkgIdKey);
                    unset($appSubscriptionPkgId);

                    if (isset($mgrStore) && $mgrStore != "") {
                        $response['data']['manager_store_details'] = $mgrStore;
                    }

                    if (isset($messages) && $messages != "") {
                        $response['data']['messages'] = $messages;
                    }

                    if (isset($txnErrorMsg) && $txnErrorMsg != "") {
                        $response['data']['transaction_err_msg'] = $txnErrorMsg;
                    }

                    if (isset($supportEmail) && $supportEmail != "") {
                        $response['data']['support_email'] = $supportEmail;
                    }

                    if (isset($blockApps) && !empty($blockApps)) {
                        $response['data']['blockApps'] = $blockApps;
                    }

                    if (isset($helpUrls) && !empty($helpUrls)) {
                        $response['data']['helpUrls'] = $helpUrls;
                    }

                    if (!empty($facebookApp)) {
                        $response['data']['facebookApp'] = $facebookApp;
                    }

                    if (!empty($loginTypeList)) {
                        $response['data']['loginTypes'] = $loginTypeList;
                    }

                    if (!empty($feedback)) {
                        $response['data']['feedback'] = $feedback;
                    }

                    if (isset($appShareUrl)) {
                        $response['data']['appShareUrl'] = $appShareUrl;
                    }

                    if (isset($versionData)) {
                        $response['data']['version'] = $versionData;
                        unset($versionData);
                    }

                    if (isset($analytics_detail)) {
                        $response['data']['analyticsDetail'] = $analytics_detail;
                        unset($analytics_detail);
                    }

                    if (isset($inAppPurchase)) {
                        $response['data']['IAPData'] = $inAppPurchase;
                        unset($inAppPurchase);
                    }

                    if (isset($externalUrl)) {
                        $response['data']['externalUrl'] = $externalUrl;
                        unset($externalUrl);
                    }

                    if (isset($maintenanceSettings)) {
                        $response['data']['maintenanceSettings'] = $maintenanceSettings;
                        unset($maintenanceSettings);
                    }

                    if (isset($menuSettings)) {
                        $response['data']['menuSettings'] = $menuSettings;
                        unset($menuSettings);
                    }

                    if (isset($playerConfiguration)) {
                        $response['data']['playerConfiguration']= $playerConfiguration;
                        unset($playerConfiguration);
                    }

                    if (isset($customConfiguration)) {
                        $response['data']['customConfiguration']= $customConfiguration;
                        unset($customConfiguration);
                    }

                    try {

                        $result = Redis::set($packageName.'-'.$platform, Utils::json($response));

                        $this->logger->info("$packageName-$platform cached successfully in redis with result ".Utils::json((array)$result));

                        Utils::printData("Cached : $packageName-$platform");
                        Utils::printData($result);
                        Utils::printData("======================================================================");

                    } catch (Predis\Connection\ConnectionException $exception) {
                        Utils::printData($exception->getMessage());
                    }
                }

                Utils::printData("Caching Done");
                exit();

            } else {
                Utils::printData("No configuration found for package $packageName");
                exit();
            }
    }
}
