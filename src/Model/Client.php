<?php

namespace Idaas\Passport\Model;

use Illuminate\Support\Str;
use Laravel\Passport\Client as LaravelClient;

class Client extends LaravelClient
{
    protected $table = 'oidc_clients';

    public $incrementing = false;
    protected $keyType = 'string';
        
    protected $primaryKey = 'client_id';

    protected $casts = [
        'contacts' => 'array',
        'grant_types' => 'array',
        'response_types' => 'array',
        'redirect_uris' => 'array',
        'post_logout_redirect_uris' => 'array',
        'code_challenge_methods_supported' => 'array',
        'trusted' => 'boolean',
        'default_prompt_allow_override' => 'boolean',
        'default_acr_values_allow_override' => 'boolean',
    ];

    protected $hidden = [
        // 'secret',
        'updated_at',
        'created_at',
        'user_id',
        'revoked',
        'personal_access_client',
        'password_client',
        'name'
    ];

    protected $appends = ['client_name'];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = (string) Str::orderedUuid();
        });
    }
    
    public function getIdAttribute()
    {
        return $this->client_id;
    }

    // Ensure compatability with the default OAuth client
    public function getClientNameAttribute()
    {
        return $this->name;
    }

    public function setClientNameAttribute($value)
    {
        $this->attributes['name'] = $value;
    }


    // Ensure compatability with the defaukt OAuth client
    public function getRedirectAttribute()
    {
        return implode(',', $this->redirect_uris);
    }
}
