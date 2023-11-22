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
    }
}
