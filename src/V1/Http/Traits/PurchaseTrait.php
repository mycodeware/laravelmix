<?php
namespace Jet\Publicam\JetEngage\V1\Http\Traits;

use config;
use Illuminate\Support\Facades\Redis;
use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;

use Jet\Publicam\JetEngage\V1\Models\Sql\UserContentPurchase;
use Jet\Publicam\JetEngage\V1\Models\Sql\ContentFirstPlayback;

/**
 * Purchase Trait
 *
 * @license         <add Licence here>
 * @link            www.jetsynthesys.com
 * @author          Imran Khan <imran.khan@jetsynthesys.com>
 * @since           Jan 23, 2019
 * @copyright       2019 Jetsynthesys Pvt Ltd.
 * @version         1.0
 */

 /**
 * Trait PurchaseTrait
 * @package Jet\Publicam\Api\JetEngage\Traits
 */
trait PurchaseTrait
{
    /**
     * Check If content is purchased or not
     * If purchased, check validity or consumption duration
     *
     * @param string $userCode
     * @param int $packageId
     * @param string $contentId
     * @return bool
     */
    public function isContentPurchasedAndValidForPlayback(string $userCode, int $packageId, string $contentId): bool
    {

        $ssid = env('SuperStoreId');

        if (stripos($userCode, $ssid."-00-000") !== false) {
            return true;
        }

        $today = new \DateTimeImmutable(
            gmdate('Y-m-d H:i', time()),
            new \DateTimeZone("UTC")
        );

        $contentStatus = UserContentPurchase::where('content_id', $contentId)
        ->where([
            'user_code' => $userCode
        ])
        ->where('valid_from', '<=', $today->format('Y-m-d'))
        ->where('valid_till', '>=', $today->format('Y-m-d'))
        ->where('purchase_status', 'Success')
        ->first();

        if ($contentStatus !== null) {
            // Get Content First Playback Time
            $getContentFirstPlaybackTime = ContentFirstPlayback::where([
                'user_code' => $userCode,
                'content_id' => $contentId
            ])
            ->first();

            if ($getContentFirstPlaybackTime === null) {
                return true;
            }

            $modify = env('StreamPlayConsumeDuration');

            $firstPlayback = strtotime((new \DateTimeImmutable(
                date('Y-m-d H:i:s', strtotime($getContentFirstPlaybackTime->first_playback_at)),
                new \DateTimeZone("UTC")
            ))->modify("+$modify hour")->format('Y-m-d H:i:s'));

            $today = new \DateTimeImmutable(gmdate('Y-m-d H:i:s', time()), new \DateTimeZone("UTC"));

            $today = strtotime($today->format('Y-m-d H:i:s'));

            if ($firstPlayback >= $today) {
                return true;
            }
        }

        return false;
    }
}
