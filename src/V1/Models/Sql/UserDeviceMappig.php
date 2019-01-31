<?php

namespace Jet\Publicam\JetEngage\V1\Models\Sql;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for User Device Mappig Table
 *
 * @license         <add Licence here>
 * @link            www.jetsynthesys.com
 * @author          Imran  Khan  <imran.khan@jetsynthesys.com>
 * @since           Jan 24, 2019
 * @copyright       2016 Jetsynthesys Pvt Ltd.
 * @version         1.0
 */
class UserDeviceMappig extends Model
{
    /** @var string Table Name */
    protected $table = 'user_device_mapping';

    /** @var bool Enable/Disable Timestamp */
    public $timestamps = true;
}
