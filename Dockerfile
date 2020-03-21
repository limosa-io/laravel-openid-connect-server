FROM php:7.4-alpine

RUN apk add --no-cache git jq moreutils
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer create-project --prefer-dist laravel/laravel example && \
    cd example

WORKDIR /example

RUN composer require moontoast/math

COPY . /laravel-openid-connect
RUN jq '.repositories=[{"type": "path","url": "/laravel-openid-connect"}]' ./composer.json | sponge ./composer.json

RUN composer require nl.idaas/laravel-openid-connect @dev

RUN touch ./.database.sqlite && \
    echo "DB_CONNECTION=sqlite" >> ./.env && \
    echo "DB_DATABASE=/example/.database.sqlite" >> ./.env && \
    echo "APP_URL=http://localhost:18124" >> ./.env

RUN php artisan migrate
RUN php artisan passport:install
RUN php artisan vendor:publish --provider="Idaas\Passport\PassportServiceProvider" --force

CMD php artisan serve --host=0.0.0.0 --port=8000
