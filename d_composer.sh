#!/bin/bash

sudo docker exec --user www-data tp_api_v2 composer "$@"
