# Laravel OpenID Connect Server

![](https://github.com/arietimmerman/laravel-openid-connect-server/workflows/CI/badge.svg)
![](https://img.shields.io/badge/license-AGPL--3.0-green)

This is an OpenID Connect Server written in PHP, built on top of [arietimmerman/openid-connect-server](https://github.com/arietimmerman/openid-connect-server) and [Laravel Passport](https://github.com/laravel/passport).

This library is __work in progress__.

## Example

~~~
docker-compose build
docker-compose up -d
~~~

Now find your `openid-configuration` at `http://localhost:18124/.well-known/openid-configuration`.
