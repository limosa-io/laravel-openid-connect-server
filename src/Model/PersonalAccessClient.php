<?php

namespace Idaas\Passport\Model;

use Illuminate\Support\Str;
use Laravel\Passport\PersonalAccessClient as PassportPersonalAccessClient;

class PersonalAccessClient extends PassportPersonalAccessClient
{

    protected $table = 'oidc_personal_access_clients';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->client_id = (string) Str::orderedUuid();
        });
    }
}
