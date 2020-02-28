<?php

namespace Idaas\Passport\Bridge;

use Idaas\OpenID\Repositories\AccessTokenRepositoryInterface;
use Laravel\Passport\Bridge\AccessTokenRepository as LaravelAccessTokenRepository;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;

class AccessTokenRepository extends LaravelAccessTokenRepository implements AccessTokenRepositoryInterface
{
    public function storeClaims($id, array $claims)
    {
        $token = $this->tokenRepository->find($id);
        $token->claims = $claims;
        $token->save();
    }

    // TODO: not needed, update AccessTokenRepositoryInterface
    public function getAccessToken($tokenId)
    {
        // TODO: not in use
    }
}
