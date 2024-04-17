.DEFAULT_GOAL := help
.PHONY: help

args = `arg="$(filter-out $(firstword $(MAKECMDGOALS)),$(MAKECMDGOALS))" && echo $${arg:-${1}}`

help:
	@grep -E '(^[a-zA-Z0-9_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

start: ## docker up
	docker-compose -f ./docker/docker-compose.yml -f ./docker/docker-compose.override.yml up -d

stop: ## docker down
	docker-compose -f ./docker/docker-compose.yml -f ./docker/docker-compose.override.yml down

restart: ## docker restart
	docker-compose -f ./docker/docker-compose.yml -f ./docker/docker-compose.override.yml down
	docker-compose -f ./docker/docker-compose.yml -f ./docker/docker-compose.override.yml up -d

rebuild: ## rebuild docker
	docker-compose -f ./docker/docker-compose.yml -f ./docker/docker-compose.override.yml up -d --force-recreate --build

bash: ## Connect to apache server
	docker-compose -f ./docker/docker-compose.yml -f ./docker/docker-compose.override.yml exec bieristo_api bash

log: ## tail -f /var/www/logs/*.log
	docker-compose -f ./docker/docker-compose.yml -f ./docker/docker-compose.override.yml exec bieristo_api "tail -f /var/www/logs/*.log"

mongo-bash: ## Connect to mongo server bash
	docker-compose -f ./docker/docker-compose.yml -f ./docker/docker-compose.override.yml exec bieristo_mongodb bash

mongo: ## Connect to mongo client
	docker-compose -f ./docker/docker-compose.yml -f ./docker/docker-compose.override.yml exec bieristo_mongodb mongosh -u bieristo -p bieristo bieristo
