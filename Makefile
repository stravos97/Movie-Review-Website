SHELL := /bin/bash

.PHONY: help up-local down-local logs-local up-remote down-remote logs-remote pull-image ps-local ps-remote verify-seed health-local health-remote print-env publish-public preflight-local preflight-remote postman

REGISTRY ?= ghcr.io
IMAGE ?= $(REGISTRY)/stravos97/Movie-Review-Website:latest

help:
	@echo "make up-local       # start local MySQL + web (seeded)"
	@echo "make down-local     # stop local stack"
	@echo "make logs-local     # tail local web logs"
	@echo "make verify-seed    # run quick SQL checks against local DB"
	@echo "make up-remote      # start web against remote DB"
	@echo "make down-remote    # stop remote stack"
	@echo "make logs-remote    # tail remote web logs"
	@echo "make pull-image     # pull latest image from GHCR"
	@echo "make ps-local       # show local stack status"
	@echo "make ps-remote      # show remote stack status"
	@echo "make health-local   # GET / on local web"
	@echo "make health-remote  # GET / on remote web compose"
	@echo "make print-env      # show sanitized env values from .env"
	@echo "make publish-public # force-push a single-commit snapshot to PUBLIC_REPO"
	@echo "make preflight-local  # validate required vars for local compose"
	@echo "make preflight-remote # validate required vars for remote compose"
	@echo "make postman         # print Postman collection path and try to open"

up-local: preflight-local
	docker compose --env-file .env -f docker-compose.local.yml up -d --build

down-local:
	docker compose --env-file .env -f docker-compose.local.yml down

logs-local:
	docker compose --env-file .env -f docker-compose.local.yml logs -f --tail=100

ps-local:
	docker compose --env-file .env -f docker-compose.local.yml ps

up-remote: preflight-remote
	docker compose --env-file .env -f docker-compose.remote.yml up -d

down-remote:
	docker compose --env-file .env -f docker-compose.remote.yml down

logs-remote:
	docker compose --env-file .env -f docker-compose.remote.yml logs -f --tail=100

ps-remote:
	docker compose --env-file .env -f docker-compose.remote.yml ps

pull-image:
	docker pull $(IMAGE)

verify-seed:
	@echo "Verifying local seed data in sparta_academy..."
	docker compose --env-file .env -f docker-compose.local.yml exec -T mysql \
	  mysql -h127.0.0.1 -u$${APP_DB_USERNAME:-sparta_user} -p$${APP_DB_PASSWORD} sparta_academy \
	  -e "SELECT 'users' AS table_name, COUNT(*) AS count FROM users; \
	      SELECT 'reviews' AS table_name, COUNT(*) AS count FROM reviews;"

health-local:
	@echo "Checking local web health at http://localhost:8080/ ..."
	@for i in $$(seq 1 30); do \
	  curl -fsS http://localhost:8080/ && exit 0; \
	  sleep 2; \
	  echo "retry $$i"; \
	 done; \
	 echo "Health check failed"; exit 1

health-remote:
	@echo "Checking remote web (compose) health at http://localhost:8080/ ..."
	@$(MAKE) -s health-local

print-env:
	@echo "Using .env values (sanitized):"
	@bash -lc 'set -a; [ -f .env ] && . ./.env; set +a; \
	  DB_URL_SHOW=$${DB_URL:-<unset>}; \
	  APP_USER_SHOW=$${APP_DB_USERNAME:-<unset>}; \
	  mask(){ [ -n "$$1" ] && printf "%*s" $${#1} "" | tr " " "*" || printf "<unset>"; }; \
	  echo "DB_URL=$$DB_URL_SHOW"; \
	  echo "APP_DB_USERNAME=$$APP_USER_SHOW"; \
	  echo -n "APP_DB_PASSWORD="; mask "$${APP_DB_PASSWORD}"; echo; \
	  echo -n "MYSQL_ROOT_PASSWORD="; mask "$${MYSQL_ROOT_PASSWORD}"; echo;' 

postman:
	@echo "Postman collection: docs/postman/MovieReview.postman_collection.json"
	@echo "Base URL variable: {{baseUrl}} (default: http://localhost:8080)"
	@if command -v uname >/dev/null 2>&1 && [ "$$(uname)" = "Darwin" ]; then \
	  open docs/postman/MovieReview.postman_collection.json || true; \
	fi

publish-public:
	@echo "Publishing sanitized snapshot to public repo (single commit history)..."
	@bash -lc 'set -e; \
	  : $${PUBLIC_REPO:=stravos97/Sparta_Global_Academy_Springboot_Public}; \
	  TMP=$$(mktemp -d); \
	  echo "> Assembling snapshot in $$TMP"; \
	  git ls-files -z | tar --null -T - -c | tar -x -C "$$TMP"; \
	  cd "$$TMP"; git init; git config user.name "local-publisher"; git config user.email "local@publisher"; \
	  git add -A; git commit -m "Public snapshot of $$(git -C "$(PWD)" rev-parse --short HEAD)"; git branch -M main; \
	  if [ -n "$$PUBLIC_REPO_TOKEN" ]; then \
	    git remote add public https://x-access-token:$$PUBLIC_REPO_TOKEN@github.com/$$PUBLIC_REPO.git; \
	  else \
	    git remote add public https://github.com/$$PUBLIC_REPO.git; \
	  fi; \
	  git push -f public main; \
	  echo "> Done: pushed single-commit snapshot to $$PUBLIC_REPO";'

preflight-local:
	@bash -lc 'set -e; \
	  if [ ! -f .env ]; then echo "ERROR: .env not found. Copy .env.example -> .env" >&2; exit 1; fi; \
	  set -a; . ./.env; set +a; \
	  missing=0; \
	  for v in APP_DB_USERNAME APP_DB_PASSWORD MYSQL_ROOT_PASSWORD APP_SECRET; do \
	    eval "val=\$$v"; \
	    if [ -z "$$val" ]; then echo "Missing $$v in .env" >&2; missing=1; fi; \
	  done; \
	  if [ "$$missing" -ne 0 ]; then echo "Preflight local FAILED" >&2; exit 1; fi; \
	  echo "Preflight local OK";'

preflight-remote:
	@bash -lc 'set -e; \
	  if [ ! -f .env ]; then echo "ERROR: .env not found. Copy .env.example -> .env" >&2; exit 1; fi; \
	  set -a; . ./.env; set +a; \
	  missing=0; \
	  for v in DB_HOST DB_PORT APP_DB_USERNAME APP_DB_PASSWORD DB_URL APP_SECRET; do \
	    eval "val=\$$v"; \
	    if [ -z "$$val" ]; then echo "Missing $$v in .env" >&2; missing=1; fi; \
	  done; \
	  if [ "$$missing" -ne 0 ]; then echo "Preflight remote FAILED" >&2; exit 1; fi; \
	  echo "Preflight remote OK";'
