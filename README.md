<div align="right">
	[![Run tests](https://github.com/Facts-and-Files/tp-api/actions/workflows/run-tests.yml/badge.svg)](https://github.com/Facts-and-Files/tp-api/actions/workflows/run-tests.yml)
</div>

# Transcribathon platform API

Version 2 of the Transcribathon platform API (tp-api) as PHP Laravel stack.

## Requirements for development

There are two docker containers/images required.

* [TP-MySQL](https://github.com/Facts-and-Files/tp-mysql)
The MySQL docker container which will provide the database (the dump will be provided on demand).
* [trenc-php](https://github.com/trenc/trenc-php)
An custom PHP image. When bulding the image use the name and tag as it is referenced in the docker-compose.yml here (scto-php:8.0).

## Development/Helpers

A Makefile is included for managing some of the processes as starting/stoping the docker container.

    $ make docker_start

    $ make docker_stop

### artisan

To get direct access to the docker internal Laravel artisan console (and not the one from the host) you can use the provided d_artisan.sh wrapper script:

    $ ./d_artisan.sh list

### Composer

Also the you can make use of the docker container internal compose with the provided wrapper:

    $ ./d_composer.sh --version

### API-Token

To access the API routes a valid token is required. The token can be generated via

    $ ./d_artisan.sh make:token

It will be stored in the `api_clients` table as hash. The token can be applied by the client as as bearer token in the header `Authorization: Bearer <api_token>`

## Development

For deployment see Makefile.
Head to https://laravel.com/docs/9.x/deployment#main-content for server requirements. Probably the PHP DOM extension is missing by default.

    $ sudo apt upate && sudo apt install php8.0-xml

If needed (on initial install) connect to the deploy server and run the

    $ php artisan migrate

manually to alter the database.

## API routes

There is an OpenAPI console available. It can be accessed via:

* local docker container: https://api.transcribathon.eu.local:4443/v2/documentation
* VPN server: https://api.transcribathon.local/v2/documentation
* DEV server: https://api.fresenia-dev.man.poznan.pl/v2/documentation

Access to all routes require a bearer token.
