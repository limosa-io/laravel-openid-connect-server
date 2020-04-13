<?php

namespace IdaasPassportTests;

use Idaas\OpenID\Entities\Traits\AccessTokenTrait;
use Laravel\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\Scope;

class AccessTokenEntity extends AccessToken
{

    use AccessTokenTrait;

    public function getClaims()
    {
        return ['test'];
    }

    public function getScopes()
    {
        return [
            new Scope('openid')
        ];
    }
}
