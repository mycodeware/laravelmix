<?php

namespace Jet\Publicam\JetEngage\V1\Models\Sql;

use Illuminate\Database\Eloquent\Model;

class EmailOtpMapping extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_otp_mapping';

    /** @var bool Enable/Disable Timestamp */
    public $timestamps = false;
}
