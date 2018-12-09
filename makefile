.phony: update shell init

DOCKER_ARGS = -v $(shell pwd):/app -e GITHUB_USER -e GITHUB_TOKEN roots/wordpress-self-update

update:
	docker run $(DOCKER_ARGS)

shell:
	docker run -it $(DOCKER_ARGS) bash

init:
	docker build -t roots/wordpress-self-update .
