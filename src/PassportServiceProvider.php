<?php

namespace Idaas\Passport;

use DateInterval;
use Idaas\OpenID\Grant\AuthCodeGrant;
use Idaas\OpenID\Repositories\ClaimRepositoryInterface;
use Idaas\OpenID\ResponseTypes\BearerTokenResponse;
use Idaas\OpenID\Session;
use Idaas\Passport\Bridge\ClaimRepository;
use Idaas\Passport\Model\Client;
use Laravel\Passport\Bridge\AuthCodeRepository;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\ScopeRepository;
use Laravel\Passport\PassportServiceProvider as LaravelPassportServiceProvider;
use League\OAuth2\Server\AuthorizationServer;

class PassportServiceProvider extends LaravelPassportServiceProvider
{

    protected function getClientModel()
    {
        return Client::class;
    }

    public function boot()
    {
        Passport::useClientModel($this->getClientModel());

        parent::boot();

        $this->app->bindIf(ClaimRepositoryInterface::class, ClaimRepository::class);
    }

    protected function makeCryptKey($type)
    {
        if ($type == 'private') {
            return resolve(KeyRepository::class)->getPrivateKey();
        } else {
            return resolve(KeyRepository::class)->getPublicKey();
        }
    }

    public function makeAuthorizationServer()
    {
        $server = new AuthorizationServer(
            $this->app->make(Bridge\ClientRepository::class),
            $this->app->make(Bridge\AccessTokenRepository::class),
            $this->app->make(ScopeRepository::class),
            resolve(KeyRepository::class)->getPrivateKey(),
            app('encrypter')->getKey(),
            new BearerTokenResponse
        );

        $server->enableGrantType(
            new AuthCodeGrant(
                $this->app->make(AuthCodeRepository::class),
                $this->app->make(RefreshTokenRepository::class),
                $this->app->make(ClaimRepositoryInterface::class),
                $this->app->make(Session::class),
                new DateInterval('PT10M'),
                new DateInterval('PT10M')
            )
        );

        // TODO: enable ImplicitGrant

        return $server;
    }
}
