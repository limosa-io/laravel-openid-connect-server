FROM php:7.4-alpine

RUN apk add --no-cache git jq moreutils yarn
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer create-project --prefer-dist laravel/laravel example && cd example

WORKDIR /example

RUN composer require moontoast/math laravel/ui

RUN cd /src; php artisan ui vue --auth && \
    yarn install && \
    yarn production

COPY . /laravel-openid-connect
RUN jq '.repositories=[{"type": "path","url": "/laravel-openid-connect"}]' ./composer.json | sponge ./composer.json

RUN composer require nl.idaas/laravel-openid-connect @dev

RUN touch ./.database.sqlite && \
    echo "DB_CONNECTION=sqlite" >> ./.env && \
    echo "DB_DATABASE=/example/.database.sqlite" >> ./.env

RUN php artisan migrate
RUN php artisan passport:install
RUN php artisan vendor:publish --provider="Idaas\Passport\PassportServiceProvider" --force

# php artisan passport:client --user_id=0 --name=op-test --redirect_uri=https://op-test:60001/authz_cb

CMD php artisan serve --host=0.0.0.0 --port=8000
