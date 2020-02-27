<?php

namespace Idaas\Passport;

use Idaas\Passport\RouteRegistrar;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport as LaravelPassport;

class Passport extends LaravelPassport
{

    public static function routes($callback = null, array $options = [])
    {
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
    }
}
