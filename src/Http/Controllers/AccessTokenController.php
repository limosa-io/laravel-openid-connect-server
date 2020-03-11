<?php

namespace Idaas\Passport\Http\Controllers;

use Idaas\Passport\Passport;
use Laminas\Diactoros\Response as Psr7Response;
use Laravel\Passport\Http\Controllers\AccessTokenController as ControllersAccessTokenController;
use Laravel\Passport\Exceptions\OAuthServerException;
use League\OAuth2\Server\CryptTrait;
use League\OAuth2\Server\Exception\OAuthServerException as LeagueException;

class AccessTokenController extends ControllersAccessTokenController
{

    use CryptTrait;

    protected function withErrorHandling($callback)
    {
        try {
            return $callback();
        } catch (LeagueException $e) {
            if ($e->getErrorType() == 'invalid_request' && $e->getHint() == 'Authorization code has been revoked') {

                // TOOD: Not needed as soon as https://github.com/thephpleague/oauth2-server/pull/1082 is used.
                $e = LeagueException::invalidGrant($e->getHint());

                // TODO: This is an ugly workaround to revoke an earlier issued access token if an authorization code
                // is used twice
                $encryptedAuthCode = $_POST['code'];
                $this->setEncryptionKey(app('encrypter')->getKey());
                $authCodePayload = \json_decode($this->decrypt($encryptedAuthCode));
                Passport::token()->where('user_id', $authCodePayload->user_id)->update(['revoked' => true]);
            }

            throw new OAuthServerException(
                $e,
                $this->convertResponse($e->generateHttpResponse(new Psr7Response))
            );
        }
    }
}
