<?php

namespace Jet\Publicam\JetEngage\V1\Models\Sql;

use Illuminate\Database\Eloquent\Model;

class UserDefaultProfile extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_default_profile';

    /** @var bool Enable/Disable Timestamp */
    public $timestamps = false;
}
