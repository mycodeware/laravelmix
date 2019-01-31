<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailOtpMapping extends Model
{
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'modified_at';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'otp','super_store_id','otp_status','otp_validity','created_at','modified_at'
    ];
 
}
