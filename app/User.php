<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
      

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';
    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'created_at';
    const remember_token = false;

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
        'name', 'email', 'password','user_code','super_store_id','store_id','user_status_id','login_social_platform_id'
    ];



    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password','remember_token'
    ];

    /**
   * Overrides the method to ignore the remember token.
   */
  public function setAttribute($key, $value)
  {
    $isRememberTokenAttribute = $key == $this->getRememberTokenName();
    if (!$isRememberTokenAttribute)
    {
      parent::setAttribute($key, $value);
    }
  }
}
