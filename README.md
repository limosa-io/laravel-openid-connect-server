# Laravel OpenID Connect Server

![](https://github.com/arietimmerman/laravel-openid-connect-server/workflows/CI/badge.svg)
![](https://img.shields.io/badge/license-AGPL--3.0-green)
[![Latest Stable Version](https://poser.pugx.org/nl.idaas/laravel-openid-connect/v/stable)](https://packagist.org/packages/nl.idaas/laravel-openid-connect)
[![Total Downloads](https://poser.pugx.org/nl.idaas/laravel-openid-connect/downloads)](https://packagist.org/packages/nl.idaas/laravel-openid-connect)

This is an OpenID Connect Server written in PHP, built on top of [arietimmerman/openid-connect-server](https://github.com/arietimmerman/openid-connect-server) and [Laravel Passport](https://github.com/laravel/passport).

This library is __work in progress__.

## Installation

~~~
composer require nl.idaas/laravel-openid-connect
php artisan migrate
php artisan passport:install --uuids
php artisan vendor:publish --provider="Idaas\Passport\PassportServiceProvider" --force
~~~

## Example

~~~
docker-compose build
docker-compose up -d
~~~

Now find your `openid-configuration` at `http://localhost:18124/.well-known/openid-configuration`.
