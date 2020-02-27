<?php

namespace Idaas\Passport;

use Idaas\Passport\Model\Provider;
use League\OAuth2\Server\Exception\OAuthServerException;
use Illuminate\Http\Request;

class ProviderRepository implements ProviderRepositoryInterface
{
    public function get()
    {
        return new Provider;
    }

    public function wellknown()
    {
        return new Provider;
    }

    public function update(Request $request)
    {
        throw OAuthServerException::invalidRequest('Not supported!');
    }
}
