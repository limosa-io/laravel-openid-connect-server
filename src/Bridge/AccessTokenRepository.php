<?php

namespace Idaas\Passport\Bridge;

use Idaas\OpenID\Repositories\AccessTokenRepositoryInterface;
use Laravel\Passport\Bridge\AccessTokenRepository as LaravelAccessTokenRepository;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Bridge\Scope;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

class AccessTokenRepository extends LaravelAccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function storeClaims($id, array $claims)
    {
        $token = $this->tokenRepository->find($id);
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
