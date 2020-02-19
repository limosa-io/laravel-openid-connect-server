<?php

namespace Idaas\Passport;

use Illuminate\Http\Request;
use ArieTimmerman\Passport\OIDC\Model\Client;
use Illuminate\Contracts\Routing\ResponseFactory;

class SessionManagementController
{
    public function logout(Request $request, ResponseFactory $response)
    {
        $redirectUri = $request->input('post_logout_redirect_uri');
        $state = $request->input('state');

        //TODO: make json column. Index and search through all. A lot faster.
        $valid = true;

        if (!empty($redirectUri)) {
            $valid = Client::select('post_logout_redirect_uris')->whereNotNull('post_logout_redirect_uris')->get()->map(function ($client) {
                return $client->post_logout_redirect_uris;
            })->collapse()->contains($redirectUri);
        }

        if (!$valid) {
            $redirectUri = null;
            $state = null;
        }
        
        return resolve(PassportConfig::class)->doLogoutResponse($request, $valid, $redirectUri, $state);
    }
}
