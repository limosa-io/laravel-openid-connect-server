<?php

namespace Idaas\Passport;

use Idaas\OpenID\RequestTypes\AuthenticationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PassportConfig
{

    /**
     * A non-null response is interpreted as if authentication is needed
     */
    public function doAuthenticationResponse(AuthenticationRequest $authenticationRequest)
    {

        Auth::check();
        
        return null;
    }

    public function doLogoutResponse(Request $request, $valid, $redirectUri, $state)
    {
        return null;
    }
}
