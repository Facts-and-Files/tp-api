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

For deployment see Makefile and the deploy.sh script.
Head to https://laravel.com/docs/9.x/deployment#main-content for server requirements. Probably the PHP DOM extension is missing by default.

    $ sudo apt upate && sudo apt install php8.0-xml

The deploy script should do most of the tasks automagically. Just run

    $ make deploy_local

to deploy the the version on the server (.local here for instance)

If needed (on initial install) connect to the deploy server and run the

    $ php artisan migrate

manually to alter the database.

## API routes

Access to all routes require a bearer token, the following routes are available by now:

* GET /api/htrdata \
List all items from htrdata
* POST /api/htrdata \
Stores a new item, body payload is in JSON format. Example: \
```
{
	"item_id": 421717, // required, related item form Items table 
	"process_id": 56845, // required, process id given by Transcribus API response
	"htr_id": 2222, // used handwriting recognition model as id, see Transcribus API
	"status": "CREATED", // current status of the Transcribus HTR process, given by Transcribus API response
	"data": "{'some':'json'}", // the data from Transcribus API response, if one as string (will be probably a XML string)
	"data_type": "json" // type of the data, probably 'xml'
}
```
* GET /api/htrdata/{id} \
Get the entry of an item
* GET /api/htrdata/byprocessid/{process_id} \
Get the entry of an item by its process id
* PUT /api/htrdata/{id} \
Update an item. Payload as JSON. Example:
```
{
	"htr_id": 421717, // id of handwriting model id, see above
	"status": "SUCCESS", // status of the HTR process
	"data": "<xml />", // data of the HTR
	"data_type": "xml" // type of the HTR data
}
```
* DELETE /api/htrdata/{id} \
Delete an item by its id.

### Example calls

    $ http --verify=no https://localhost/api/htrdata 'Authorization: Bearer <api_token>'

    $ http --verify=no https://localhost/api/htrdata/byprocessid/56845 'Authorization: Bearer <api_token>'

    $ http POST --verify=no https://localhost/api/htrdata 'Authorization: Bearer <api_token>' @post.json
