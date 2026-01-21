#!/bin/bash

docker exec --user www-data tp_api_v2 php artisan "$@"
