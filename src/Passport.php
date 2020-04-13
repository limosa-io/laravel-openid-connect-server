<?php

namespace Idaas\Passport;

use Idaas\Passport\RouteRegistrar;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport as LaravelPassport;

class Passport extends LaravelPassport
{

    public static function routes($callback = null, array $options = [])
    {
        $registerWellKnown = $callback == null;
        $callback = $callback ?: function ($router) {
            $router->all();
        };

        $defaultOptions = [
            'prefix' => 'oauth',
            'namespace' => '\Laravel\Passport\Http\Controllers',
        ];

        $options = array_merge($defaultOptions, $options);

        Route::group($options, function ($router) use ($callback) {
            $callback(new RouteRegistrar($router));
        });

        // The wellKnown endpoints must be registered without a prefix
        if ($registerWellKnown) {
            Route::group([
                'namespace' => '\Laravel\Passport\Http\Controllers'
            ], function ($router) use ($callback) {
                $router = new RouteRegistrar($router);
                $router->forWellKnown();
            });
        }
    }
}
