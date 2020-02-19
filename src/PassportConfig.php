<?php

namespace Idaas\Passport;

use Idaas\OpenID\RequestTypes\AuthenticationRequest;
use Illuminate\Http\Request;

class PassportConfig
{
    public function doAuthenticationResponse(AuthenticationRequest $authenticationRequest)
    {
        return null;
    }

    public function doLogoutResponse(Request $request, $valid, $redirectUri, $state)
    {
        return null;
    }
}
