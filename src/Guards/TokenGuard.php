<?php

namespace Idaas\Passport\Guards;

use Illuminate\Http\Request;
use Laravel\Passport\Guards\TokenGuard as GuardsTokenGuard;

class TokenGuard extends GuardsTokenGuard
{

    public function user(Request $request)
    {
        $result = parent::user($request);

        /**
         * Support for https://tools.ietf.org/id/draft-ietf-oauth-v2-bearer-00.html#body-param
         */
        if (($access_token = $request->input('access_token')) != null) {
            $request->headers->set('Authorization', 'Bearer ' . $access_token);
            $result = $this->authenticateViaBearerToken($request);
        }

        return $result;
    }
}
