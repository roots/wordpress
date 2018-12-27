.phony: update shell init

DOCKER_TAG = austinpray/roots-wordpress-updater
DOCKER_ARGS = -v $(shell pwd):/app -e GITHUB_USERNAME -e GITHUB_TOKEN $(DOCKER_TAG)

update:
	docker run $(DOCKER_ARGS)

shell:
	docker run -it $(DOCKER_ARGS) bash

init:
	docker pull $(DOCKER_TAG)
	docker build --cache-from $(DOCKER_TAG) -t $(DOCKER_TAG) .
