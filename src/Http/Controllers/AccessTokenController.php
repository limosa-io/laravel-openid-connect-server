<?php

/**
 * Not needed as soon as https://github.com/thephpleague/oauth2-server/pull/1082 is used. 
 */

namespace Idaas\Passport\Http\Controllers;

use Laminas\Diactoros\Response as Psr7Response;
use Laravel\Passport\Http\Controllers\AccessTokenController as ControllersAccessTokenController;
use Laravel\Passport\Exceptions\OAuthServerException;
use League\OAuth2\Server\Exception\OAuthServerException as LeagueException;

class AccessTokenController extends ControllersAccessTokenController
{

    protected function withErrorHandling($callback)
    {
        try {
            return $callback();
        } catch (LeagueException $e) {

            if ($e->getErrorType() == 'invalid_request' && $e->getHint() == 'Authorization code has been revoked') {
                $e = LeagueException::invalidGrant($e->getHint());
            }

            throw new OAuthServerException(
                $e,
                $this->convertResponse($e->generateHttpResponse(new Psr7Response))
            );
        }
    }
}
