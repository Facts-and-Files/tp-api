# Transcribathon platform API

Version 2 of the Transcribathon platform API (tp-api) as PHP Laravel stack.

## Requirements

There are two docker containers/images required.

* [TP-MySQL](https://github.com/Facts-and-Files/tp-mysql)
The MySQL docker container which will provide the database (the dump will be provided on demand).
* [trenc-php](https://github.com/trenc/trenc-php)
An custom PHP image. When bulding the image use the name and tag as it is referenced in the docker-compose.yml here (scto-php:7.4).

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
