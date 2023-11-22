<?php

namespace Idaas\Passport;

use Idaas\Passport\RouteRegistrar;
use Illuminate\Support\Facades\Route;
use Laravel\Passport\Passport as LaravelPassport;

class Passport extends LaravelPassport
{
    /**
     * Register the routes needed for authorization.
     *
     * @deprecated - routes method is removed at Passport 11.
     * <br> Laravel Passport's routes are now registered in service provider and uses web.php file.
     * <br> To define routes this way we have to use Passport::ignoreRoutes() method and copy out the routes from
     * Passport's web.php file. See: https://github.com/laravel/passport/pull/1464
     * @param  callable|null  $callback
     * @param  array  $options
     * @return void
     */
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
