<?php

namespace Idaas\Passport\Bridge;

use Idaas\OpenID\Entities\AccessTokenEntityInterface;
use Laravel\Passport\Bridge\AccessToken as BridgeAccessToken;
use League\OAuth2\Server\Entities\ClientEntityInterface;

class AccessToken extends BridgeAccessToken implements AccessTokenEntityInterface
{

    protected $claims;

    public function __construct($userIdentifier, array $scopes, ClientEntityInterface $client, array $claims)
    {
        parent::__construct($userIdentifier, $scopes, $client);

        $this->claims = $claims;
    }

    /**
     * Return an array of scopes associated with the token.
     *
     * @return ClaimEntityInterface[]
     */
    public function getClaims()
    {
        return $this->claims;
    }
}
