<?php

namespace Jet\Publicam\JetEngage\V1\Models\Sql;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /** @var bool Enable/Disable Timestamp */
    public $timestamps = false;


}
