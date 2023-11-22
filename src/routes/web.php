<?php

use Illuminate\Support\Facades\Route;

Route::post('/token', [
    'uses' => 'AccessTokenController@issueToken',
    'as' => 'token',
    'middleware' => 'throttle',
]);

Route::group(['middleware' => ['web']], function ($router) {
    $router->get('/authorize', [
        'uses' => '\Idaas\Passport\Http\Controllers\AuthorizationController@authorize',
    ])->name('oauth.authorize');

    $router->get('/logout', [
        'uses' => '\Idaas\Passport\SessionManagementController@logout',
    ])->name('oidc.logout');
});

Route::group([], function ($router) {
    $router->match(['get', 'post'], '/userinfo', [
        'uses' => '\Idaas\Passport\UserInfoController@userinfo',
    ])->name('oidc.userinfo');
});

Route::group([], function ($router) {
    $router->post('/introspect', [
        'uses' => '\Idaas\Passport\IntrospectionController@introspect',
    ])->name('oauth.introspect');

    $router->post('/revoke', [
        'uses' => '\Idaas\Passport\RevokeController@index',
    ])->name('oauth.revoke');
});

Route::group(['middleware' => ['api']], function ($router) {
    $router->get('/oidc/provider', [
        'uses' => '\Idaas\Passport\ProviderController@index',
    ]);

    $router->put('/oidc/provider', [
        'uses' => '\Idaas\Passport\ProviderController@update',
    ]);
});

// For OIDCClient
Route::group(['middleware' => ['api']], function ($router) {
    $router->get('/connect/register', [
        'uses' => '\Idaas\Passport\ClientController@forUser',
    ])->name('oidc.manage.client.list');

    $router->post('/connect/register', [
        'uses' => '\Idaas\Passport\ClientController@store',
    ])->name('oidc.manage.client.create');

    // Not in the specs, yet useful
    $router->get('/connect/register/{client_id}', [
        'uses' => '\Idaas\Passport\ClientController@get',
    ])->name('oidc.manage.client.get');

    $router->put('/connect/register/{client_id}', [
        'uses' => '\Idaas\Passport\ClientController@update',
    ])->name('oidc.manage.client.replace');

    $router->delete('/connect/register/{client_id}', [
        'uses' => '\Idaas\Passport\ClientController@destroy',
    ])->name('oidc.manage.client.delete');
});

$guard = config('passport.guard', null);