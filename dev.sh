#!/usr/bin/env bash

if [[ ! -f .env ]]; then
    echo "Please copy the file .env.dist to .env and configure for your needs!" >&2
    (return 2>/dev/null) && return 1 || exit 1
fi

if [[ ! -f app/app.env ]]; then
    echo "Please copy the file app/app.env.dist to app/app.env and configure for your needs! (runs out of the box)" >&2
    (return 2>/dev/null) && return 1 || exit 1
fi

if [[ ! -f app/config/autoload/local.php ]]; then
    cp app/config/autoload/local.php.dist app/config/autoload/local.php
fi

if [[ ! -d app/vendor ]]; then
    # wait until composer dependencies are installed
    docker-compose run --rm pb-ces-composer
fi

if [[ ! -d cody/vendor ]]; then
    # wait until composer dependencies are installed
    docker-compose run --rm pb-ces-composer-cody
fi

docker-compose up -d --no-recreate

docker-compose ps
