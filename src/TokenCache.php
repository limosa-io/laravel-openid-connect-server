<?php

namespace Idaas\Passport;

class TokenCache
{

    /**
     * Unset this cache if the token gets revoked. Or client deleted/revoked/updated
     */
    public function rememberUserInfo(string $tokenId, $closure)
    {
        return ($closure)();
    }

    /**
     * Unset this cache if the token gets revoked. Or client deleted/revoked/updated
     */
    public function rememberUser(string $tokenId, $closure)
    {
        return ($closure)();
    }

    /**
     * Unset this cache if client gets deleted/revoked/updated
     */
    public function rememberOriginAllowed(?string $origin, $closure)
    {
        return ($closure)();
    }
}
