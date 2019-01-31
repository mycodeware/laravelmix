<?php

namespace Jet\Publicam\JetEngage\V1\Models\Sql;

use Jet\Publicam\JetEngage\V1\Models\Sql\LoginSocialPlatform;
use Illuminate\Database\Eloquent\Model;

class StoreLoginSocialPlatformMapping extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'store_login_social_platform_mapping';

    /**
     * StoreLoginSocialPlatformMapping relation with LoginSocialPlatform Model
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo LoginSocialPlatform Model
     */
    public function platform()
    {
        return $this->belongsTo(LoginSocialPlatform::class, 'login_social_platform_id');
    }
}
