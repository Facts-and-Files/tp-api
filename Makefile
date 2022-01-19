.PHONY: docker_start docker_stop

docker_start:
	@echo "Starting database container..."
	sudo docker-compose -f ../tp-mysql/docker-compose.yml up -d
	@echo "Starting PHP/Apache container..."
	sudo docker-compose up -d
	@echo "----"
	@echo "Webserver running on https://localhost/"
	@echo "Mailhog running on http://localhost:8025"
	@echo "----"

docker_stop:
	@echo "Stopping all containers..."
	sudo docker-compose -f ./docker-compose.yml -f ../tp-mysql/docker-compose.yml down
