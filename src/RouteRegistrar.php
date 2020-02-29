<?php

namespace Idaas\Passport;

use Laravel\Passport\RouteRegistrar as LaravelRouteRegistrar;

class RouteRegistrar extends LaravelRouteRegistrar
{
    public function forAuthorization()
    {
        $this->router->group(['middleware' => ['web']], function ($router) {
            $router->get('/authorize', [
                'uses' => '\Idaas\Passport\Http\Controllers\AuthorizationController@authorize',
            ])->name('oauth.authorize');


            $router->get('/logout', [
                'uses' => '\Idaas\Passport\SessionManagementController@logout',
            ])->name('oidc.logout');
        });
    }

    public function forUserinfo()
    {
        $this->router->group([], function ($router) {
            $this->router->match(['get', 'post'], '/userinfo', [
                'uses' => '\Idaas\Passport\UserInfoController@userinfo',
            ])->name('oidc.userinfo');
        });
    }

    public function forIntrospect()
    {
        $this->router->group([], function ($router) {
            $this->router->post('/introspect', [
                'uses' => '\Idaas\Passport\IntrospectionController@introspect',
            ])->name('oauth.introspect');

            $this->router->post('/revoke', [
                'uses' => '\Idaas\Passport\RevokeController@index',
            ])->name('oauth.revoke');
        });
    }

    public function forLogout()
    {
        $this->router->group(['middleware' => ['web']], function ($router) {
            $router->get('/logout', [
                'uses' => '\Idaas\Passport\SessionManagementController@logout',
            ])->name('oidc.logout');
        });
    }

    public function forWellKnown()
    {
        $this->router->group([], function ($router) {
            $router->get('/.well-known/openid-configuration', [
                'uses' => '\Idaas\Passport\ProviderController@wellknown',
            ])->name('oidc.configuration');

            $router->get('/.well-known/jwks.json', [
                'uses' => '\Idaas\Passport\ProviderController@jwks',
            ])->name('oidc.jwks');

            $router->get('/.well-known/webfinger', [
                'uses' => '\Idaas\Passport\ProviderController@webfinger',
            ])->name('oidc.webfinger');
        });
    }

    public function forManagement()
    {
        $this->router->group(['middleware' => ['api']], function ($router) {
            $router->get('/oidc/provider', [
                'uses' => '\Idaas\Passport\ProviderController@index',
            ]);

            $router->put('/oidc/provider', [
                'uses' => '\Idaas\Passport\ProviderController@update',
            ]);
        });
    }

    public function forOIDCClients()
    {
        $this->router->group(['middleware' => ['api']], function ($router) {
            $router->get('/connect/register', [
                'uses' => 'ClientController@forUser',
            ])->name('oidc.manage.client.list');

            $router->post('/connect/register', [
                'uses' => 'ClientController@store',
            ])->name('oidc.manage.client.create');

            // Not in the specs, yet useful
            $router->get('/connect/register/{client_id}', [
                'uses' => 'ClientController@get',
            ])->name('oidc.manage.client.replace');

            $router->put('/connect/register/{client_id}', [
                'uses' => 'ClientController@update',
            ])->name('oidc.manage.client.replace');

            $router->delete('/connect/register/{client_id}', [
                'uses' => 'ClientController@destroy',
            ])->name('oidc.manage.client.delete');
        });
    }

    /**
     * Register the routes for retrieving and issuing access tokens.
     *
     * @return void
     */
    public function forAccessTokens()
    {
        $this->router->post('/token', [
            'uses' => 'AccessTokenController@issueToken',
            'middleware' => 'throttle',
        ])->name('oauth.token');

        $this->router->group(['middleware' => ['web', 'auth']], function ($router) {
            $router->delete('/tokens/{token_id}', [
                'uses' => 'AuthorizedAccessTokenController@destroy',
            ]);
        });
    }
}
