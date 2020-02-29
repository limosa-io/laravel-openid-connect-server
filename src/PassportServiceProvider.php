<?php

namespace Idaas\Passport;

use DateInterval;
use Idaas\OpenID\Grant\AuthCodeGrant;
use Idaas\OpenID\Grant\ImplicitGrant;
use Idaas\OpenID\Repositories\ClaimRepositoryInterface;
use Idaas\OpenID\Repositories\UserRepositoryInterface;
use Idaas\OpenID\ResponseTypes\BearerTokenResponse;
use Idaas\OpenID\Session;
use Idaas\Passport\Bridge\AccessTokenRepository;
use Idaas\OpenID\Repositories\AccessTokenRepositoryInterface;
use Idaas\Passport\Bridge\ClaimRepository;
use Idaas\Passport\Bridge\UserRepository;
use Idaas\Passport\Model\Client;
use Laravel\Passport\Bridge\AccessTokenRepository as BridgeAccessTokenRepository;
use Laravel\Passport\Bridge\AuthCodeRepository;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\ScopeRepository;
use Laravel\Passport\PassportServiceProvider as LaravelPassportServiceProvider;
use League\OAuth2\Server\AuthorizationServer;
use League\OAuth2\Server\AuthorizationValidators\AuthorizationValidatorInterface;
use League\OAuth2\Server\ResourceServer;

class PassportServiceProvider extends LaravelPassportServiceProvider
{

    protected function getClientModel()
    {
        return Client::class;
    }

    public function boot()
    {
        Passport::useClientModel($this->getClientModel());
        // Passport::useTokenModel()

        parent::boot();

        $this->app->bindIf(ClaimRepositoryInterface::class, ClaimRepository::class);
        $this->app->bindIf(UserRepositoryInterface::class, UserRepository::class);

        $this->app->singleton(AccessTokenRepositoryInterface::class, function ($app) {
            return $this->app->make(AccessTokenRepository::class);
        });
        $this->app->singleton(BridgeAccessTokenRepository::class, function ($app) {
            return $app->make(AccessTokenRepositoryInterface::class);
        });
    }

    protected function makeCryptKey($type)
    {
        if ($type == 'private') {
            return resolve(KeyRepository::class)->getPrivateKey();
        } else {
            return resolve(KeyRepository::class)->getPublicKey();
        }
    }

    protected function registerResourceServer()
    {
        $this->app->singleton(ResourceServer::class, function () {
            // TODO: consider using AdvancedResourceServer
            return new ResourceServer(
                $this->app->make(Bridge\AccessTokenRepository::class),
                $this->makeCryptKey('public')
            );
        });
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

        $server->enableGrantType(
            new ImplicitGrant(
                $this->app->make(UserRepositoryInterface::class),
                $this->app->make(ClaimRepositoryInterface::class),
                new DateInterval('PT10M'),
                new DateInterval('PT10M')
            )
        );

        return $server;
    }
}
