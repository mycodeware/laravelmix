<?php

namespace Jet\Publicam\JetEngage\V1\Http\Controllers\Utility;

use Jet\Publicam\JetEngage\V1\Http\Controllers\Utility\Utils;
use Jet\Publicam\JetEngage\V1\Models\Sql\UserDefaultProfile;

/**
 * Validation Class
 *
 * @library			Publicam
 *
 * @license 		<add Licence here>
 * @link			www.jetsynthesys.com
 * @author			Imran Khan 	<imran.khan@jetsynthesys.com>
 * @since			Jan 17, 2019
 * @copyright		2016 Jetsynthesys Pvt Ltd.
 * @version			1.0
 */
class Validation
{
    /**
     * Check input is valid json or not
     *
     * @param string $string
     * @return bool
     */
    public static function isJson(string $string): bool
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    /**
     * Check whether User exists or not
     *
     * @param int $storeId
     * @param string $userCode
     * @return bool
     */
    public static function userExists(int $storeId, string $userCode): bool
    {
        $profileExists = new UserDefaultProfile;

        return $profileExists->where([
            'user_code' => $userCode,
            'super_store_id' => $storeId
        ])->exists();
    }
}
