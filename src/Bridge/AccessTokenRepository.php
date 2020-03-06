<?php

namespace Idaas\Passport\Bridge;

use Idaas\OpenID\Repositories\AccessTokenRepositoryInterface;
use Idaas\Passport\Bridge\AccessToken;
use Laravel\Passport\Bridge\AccessToken as BridgeAccessToken;
use Laravel\Passport\Bridge\AccessTokenRepository as LaravelAccessTokenRepository;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Bridge\Scope;

class AccessTokenRepository extends LaravelAccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function storeClaims(BridgeAccessToken $token, array $claims)
    {
        
        $token = $this->tokenRepository->find($token->getIdentifier());
        $token->claims = $claims;
        $token->save();
    }

    public function getAccessToken($id)
    {
        $token = $this->tokenRepository->find($id);

        return new AccessToken(
            $token->user_id,
            collect($token->scopes)->map(function ($scope) {
                return new Scope($scope);
            })->toArray(),
            new Client('not used', 'not used', 'not used', false),
            $token->claims ?? []
        );
    }
}
