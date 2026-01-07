.PHONY: all distclean help lint lint-fix serve stop test vendor

# include .env
# export $(shell sed 's/=.*//' .env)

PWD := $(shell pwd)
TP_API_DB := ../tp-mysql
SOLR := ../tp-solr

all: test

distclean:
	@rm -rf ./src/vendor ./src/composer.lock

serve:
	@cd $(TP_API_DB) && docker compose up --detach
	@cd $(SOLR) && docker compose up --detach
	@cd $(PWD) && docker compose up --detach
	@echo
	@echo "API database running on tp_mysql:3306"
	@echo "SOLR is available on tp_solr:8983"
	@echo "Webserver running on https://api.transcribathon.eu.local:4443/v2/"
	@echo
	@echo "I'm up to no good..."
	@echo

stop:
	@echo
	@echo "Stopping all containers..."
	@cd $(SOLR) && docker compose down
	@cd $(TP_API_DB) && docker compose down
	@cd $(PWD) && docker compose down
	@echo
	@echo "...mischief managed."
	@echo

test: serve
	@clear
	@bash docker_artisan.sh test --without-tty --colors=always

lint: serve
	@clear
	./src/vendor/bin/pint --test -v src/app src/tests src/routes --preset psr12

lint-fix: serve
	@clear
	./src/vendor/bin/pint src/app src/tests src/routes --preset psr12

vendor: distclean
	@clear
	@bash docker_composer.sh install

help:
	@echo "Manage project"
	@echo ""
	@echo "Usage:"
	@echo "  $$ make [command]"
	@echo ""
	@echo "Commands:"
	@echo ""
	@echo "  $$ make lint"
	@echo "  Lint code style"
	@echo ""
	@echo "  $$ make lint-fix"
	@echo "  Lint and fix code style"
	@echo ""
	@echo "  $$ make distclean"
	@echo "  Delete installed dependencies"
	@echo ""
	@echo "  $$ make serve"
	@echo "  Starting the servers"
	@echo ""
	@echo "  $$ make stop"
	@echo "  Stopping the servers"
	@echo ""
	@echo "  $$ make test"
	@echo "  Run tests"
	@echo ""
	@echo "  $$ make vendor"
	@echo "  Install dependencies"
	@echo ""
