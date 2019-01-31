<?php
namespace Jet\Publicam\JetEngage\V1\Models\Sql;

use Illuminate\Database\Eloquent\Model;

/**
 * Model for Device Detail Table
 *
 * @license         <add Licence here>
 * @link            www.jetsynthesys.com
 * @author          Riyaz Patwegar  <riyaz.patwegar@jetsynthesys.com>
 * @since           Jan 23, 2019
 * @copyright       2019 Jetsynthesys Pvt Ltd.
 * @version         1.0
 */
class UserFavouriteContent extends Model
{
    /** @var string Connection Name */
    //protected $connection = 'default';

    /** @var string Table Name */
    protected $table = 'user_favourite_content';

    /** @var bool Enable/Disable Timestamp */
    public $timestamps = true;
}
