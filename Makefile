.PHONY: docker_start docker_stop

pwd := $(shell pwd)

docker_start:
	@echo "Starting database container..."
	cd ../tp-mysql && sudo docker-compose up -d
	@echo "Starting PHP/Apache container..."
	cd $(pwd) && sudo docker-compose up -d
	@echo "----"
	@echo "Webserver running on https://localhost/"
	@echo "Mailhog running on http://localhost:8025"
	@echo "----"

docker_stop:
	@echo "Stopping all containers..."
	cd ../tp-mysql && sudo docker-compose down
	cd $(pwd) && sudo docker-compose down
