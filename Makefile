# include .env
# export $(shell sed 's/=.*//' .env)

this_container := $(shell pwd)
api_db_container := ../tp-mysql

docker_start:
	@echo "Starting database container..."
	@cd $(api_db_container) && sudo docker-compose up -d
	@echo "Starting PHP/Apache container..."
	@cd $(this_container) && sudo docker-compose up -d
	@echo
	@echo "API database running on tp_mysql:3306"
	@echo "Webserver running on https://api.transcribathon.eu.local:4443/v2/"
	@echo
	@echo "I'm up to no good..."
	@echo

docker_stop:
	@echo
	@echo "Stopping all containers..."
	@cd $(api_db_container) && sudo docker-compose down
	@cd $(this_container) && sudo docker-compose down
	@echo
	@echo "...mischief managed."
	@echo

deploy_local:
	@echo "deploying to local..."
	@bash deploy.sh local
	@echo "...deploying done"

deploy_dev:
	@echo "deploying to DEV..."
	@bash deploy.sh dev
	@echo "...deploying done"
