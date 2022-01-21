#!/bin/bash

sudo docker exec --user www-data tp-api_php_apache_1 php artisan "$@"
