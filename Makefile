.PHONY: docker_start docker_stop

pwd := $(shell pwd)
db_path := ../tp-mysql

docker_start:
	@echo "Starting database container..."
	cd $(db_path) && sudo docker-compose up -d
	@echo "Starting PHP/Apache container..."
	cd $(pwd) && sudo docker-compose up -d
	@echo "----"
	@echo "MySQL running on db:3306"
	@echo "Webserver running on https://localhost/"
	@echo "Mailhog running on http://localhost:8025"
	@echo "----"

docker_stop:
	@echo "Stopping all containers..."
	cd $(db_path) && sudo docker-compose down
	cd $(pwd) && sudo docker-compose down
