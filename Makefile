.PHONY: docker_start docker_stop

this_container := $(shell pwd)
api_db_container := ../tp-mysql

docker_start:
	@echo "Starting database container..."
	cd $(api_db_container) && sudo docker-compose up -d
	@echo "Starting PHP/Apache container..."
	cd $(this_container) && sudo docker-compose up -d
	@echo
	@echo "----"
	@echo "API database running on tp_mysql:3306"
	@echo "Webserver running on https://localhost/"
	@echo "Mailhog running on http://localhost:8025"
	@echo "----"
	@echo "I'm up to no good..."
	@echo

docker_stop:
	@echo
	@echo "----"
	@echo "Stopping all containers..."
	cd $(api_db_container) && sudo docker-compose down
	cd $(this_container) && sudo docker-compose down
	@echo "----"
	@echo "...mischief managed."
	@echo
