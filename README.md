# Movie-Review-Website

[![CI Pipeline](https://github.com/stravos97/Movie-Review-Website/actions/workflows/ci.yml/badge.svg)](https://github.com/stravos97/Movie-Review-Website/actions/workflows/ci.yml)

Web application allows users to register, log in/out, create, view and edit movie reviews. The information about the review includes movie title, director, list of actors,
running time, reviewer and the actual review.

Helpful docs:
- Architecture overview: [architecture.md](architecture.md)
- CI/CD workflows: [WORKFLOWS.md](WORKFLOWS.md)

## Quick Start Options

Choose one of the following ways to run the app:

| Method                           | Difficulty        | Best For                              | Time Required |
|----------------------------------|-------------------|---------------------------------------|---------------|
| Docker with Pre-built Images     | ★☆☆ (Easiest)     | Production-like setup, no build       | 2 minutes     |
| Docker with Local MySQL          | ★★☆ (Recommended) | Dev with control over database        | 3 minutes     |
| Local PHP (no Docker)            | ★★★ (Traditional) | Full control over environment         | 5–10 minutes  |

### Prerequisites

- Docker Desktop (for Docker flows)
- Or: PHP 8.1+ (or 7.4+), Composer 2.x, MySQL 8.0

### Initial Setup

```bash
git clone https://github.com/stravos97/Movie-Review-Website.git
cd Movie-Review-Website
cp .env.example .env
# Edit .env: set APP_DB_PASSWORD, MYSQL_ROOT_PASSWORD, APP_SECRET
make print-env   # optional sanity check (sanitized output)
```

### Method 1: Docker with Pre-built Images

```bash
# Start local stack (MySQL + web, seeded)
make up-local

# Or connect to remote DB (requires DB_URL in .env)
make up-remote

# Open the site
open http://localhost:8080/  # macOS (or browse manually)

# Logs / Stop
make logs-local
make down-local
```

Image tags on GHCR:
- ghcr.io/stravos97/movie-review-website:latest
- ghcr.io/stravos97/movie-review-website:sha-<commit>

### Method 2: Run Locally (No Docker)

```bash
composer install
# Point DATABASE_URL in .env to your MySQL, e.g.:
# DATABASE_URL=mysql://sparta_user:password@127.0.0.1:3306/sparta_academy?serverVersion=8.0

# Create schema (option A: Doctrine migrations if present)
# php bin/console doctrine:migrations:migrate
# Option B: import provided schema
mysql -usparta_user -p sparta_academy < schema_sparta.sql

# Run the app
php -S 127.0.0.1:8000 -t public
```

### First Login and Review

- Login: http://localhost:8080/login (see Default Login below)
- Or register: http://localhost:8080/register
- Create review: http://localhost:8080/article/new

Key features implemented:

Non-authenticated users should not be able to write/edit reviews but should be able to read them.

The application should allow multiple users to review a single movie. Thus, meaning a user should be able to add a movie if it does not exist, and then other people should be able to write
reviews about that movie that has been created.

The application should be well-structured, implementing:

• Multiple role levels (e.g. user, moderator and administrator)
• Search functionality
• Review rating system
• Image upload

## Environment Setup

- Copy the example file and fill values (do not commit secrets):

```bash
cp .env.example .env
```

- Local Docker (docker-compose.local.yml):
  - Required in `.env`: `APP_DB_USERNAME`, `APP_DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `APP_SECRET`
  - Optional: leave `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_NAME=sparta_academy`

- Remote Docker (docker-compose.remote.yml):
  - Required in `.env`: `DB_HOST`, `DB_PORT`, `APP_DB_USERNAME`, `APP_DB_PASSWORD`, `APP_SECRET`
  - Set `DB_URL` to the full DSN used by the container (Compose passes it to Symfony):

```bash
# Example (adjust host and password)
DB_HOST=204.197.161.186
DB_PORT=3306
APP_DB_USERNAME=sparta_user
APP_DB_PASSWORD=your_secure_password
DB_NAME=sparta_academy
DB_URL=mysql://${APP_DB_USERNAME}:${APP_DB_PASSWORD}@${DB_HOST}:${DB_PORT}/${DB_NAME}?serverVersion=8.0
```

- Symfony without Docker:
  - Use `.env` (gitignored) for your local secrets; do not commit it.
  - Ensure `DATABASE_URL` is set to a valid DSN (it defaults to `DB_URL`).

## Docker Usage

Quick start with the Makefile helpers:

```bash
# Start local stack (MySQL + web, seeded)
make up-local

# Check the container is serving
make health-local   # hits http://localhost:8080/

# Tail logs
make logs-local

# Stop everything
make down-local
```

Useful extras:

```bash
make ps-local       # show container status
make verify-seed    # run basic SQL checks in local DB
```

## Default Login (Local Docker)

When running with `docker-compose.local.yml`, the MySQL container seeds a couple of users for convenience:

- Admin: `admin@example.com` — password: `Password123!` (roles: `ROLE_ADMIN, ROLE_USER`)
- User: `alice@example.com` — password: `Password123!` (role: `ROLE_USER`)

- Login page: visit `/login` (route name `website_login`).

Notes:
- These credentials are intended for local use only — change them for any non-local deployment.
- The SQLite database used in CI is not seeded; register via `/register` if needed when running tests interactively.

## CI

- GitHub Actions workflow: `.github/workflows/ci.yml`
- Runs on every push/PR:
  - Installs dependencies
  - Prepares SQLite test DB and schema
  - Runs PHPUnit tests in `tests/`
  - Runs lightweight Symfony/Composer/Doctrine checks
- On `main`, a Docker image is built and pushed to GHCR if tests pass.

### Running tests locally

```bash
composer install
APP_ENV=test DATABASE_URL="sqlite:///%cd%/var/test.db" php bin/console doctrine:schema:create -q
vendor/bin/phpunit --testdox
```

## HTTP Endpoints (API)

This project is primarily server‑rendered HTML. The only JSON endpoint is the health probe. Below are the main routes and how to exercise them.

- GET `/health`
  - Returns JSON app health.
  - Example:
    
    ```bash
    curl -fsS http://localhost:8080/health | jq .
    # { "status": "ok", "db": "up" }
    ```

- GET `/` (Homepage)
  - Lists recent reviews (HTML).

- GET `/article/{id}` (Show review)
  - Displays a single review (HTML).

- GET `/article/new` (New review form)
  - Auth required. Submitting the form performs a POST to the same path with CSRF protection (browser flow).

- GET `/article/edit/{id}` (Edit review form)
  - Auth required; owner‑only in normal setups.

- DELETE `/article/delete/{id}`
  - Auth required. Typically triggered by a browser/form/JS with CSRF token.

- GET `/search?q=term[&page=1]`
  - HTML search results. Full‑text when available, otherwise partial match.
  - Example:
    
    ```bash
    curl -fsS "http://localhost:8080/search?q=matrix" | head -n 20
    ```

- GET `/me/reviews[?page=1]`
  - Auth required. Lists your reviews (HTML).

- GET `/me/comments[?page=1]`
  - Auth required. Lists your comments (HTML).

- GET `/_menu/user`
  - Returns a small HTML fragment for the user dropdown when authenticated; 204 No Content when not logged in.

Notes
- Authenticated actions use cookie‑based login with CSRF tokens; there is no public REST API surface for creating/editing via JSON.
- If you need a JSON API, consider adding dedicated controllers that return JSON and accept token‑based auth (e.g., JWT) while keeping the existing browser flows intact.

## Makefile Commands Reference

| Command              | Description                                   |
|----------------------|-----------------------------------------------|
| `make up-local`      | Start local MySQL + web (seeded)               |
| `make down-local`    | Stop local stack                               |
| `make logs-local`    | Tail local web logs                            |
| `make ps-local`      | Show local stack status                        |
| `make verify-seed`   | Verify database seed (counts rows)             |
| `make up-remote`     | Start web against remote DB                    |
| `make down-remote`   | Stop remote-connected stack                    |
| `make logs-remote`   | Tail remote-connected web logs                 |
| `make ps-remote`     | Show remote stack status                       |
| `make pull-image`    | Pull latest Docker image from GHCR             |
| `make health-local`  | GET / against local web                        |
| `make health-remote` | GET / against remote-connected web             |
| `make print-env`     | Show sanitized values from `.env`              |
| `make preflight-local`  | Validate required vars for local compose   |
| `make preflight-remote` | Validate required vars for remote compose  |

## Postman Collection

A Postman collection is provided to exercise key endpoints quickly.

- File: `docs/postman/MovieReview.postman_collection.json`
- Base URL variable: `{{baseUrl}}` (defaults to `http://localhost:8080`)
  - Tip: `make postman` prints the file path and (on macOS) opens it

Import into Postman and run requests for:
- Health: `GET {{baseUrl}}/health`
- Homepage: `GET {{baseUrl}}/`
- Search: `GET {{baseUrl}}/search?q=matrix`
- Login page (HTML): `GET {{baseUrl}}/login`
- New review (HTML): `GET {{baseUrl}}/article/new` (requires login in browser flow)

## Troubleshooting (Symfony)

- 500 on `/health` or first request (permissions)
  - Ensure `var/` is writable. In Docker: `docker exec -it movie_review_web sh -lc 'chown -R www-data:www-data var && chmod -R 775 var'`
  - Clear cache: `php bin/console cache:clear --env=prod` (or inside container)

- Database connection errors (Doctrine DBAL SQLSTATE)
  - Verify `DATABASE_URL` (or `DB_URL`) in `.env`
  - For Docker local: ensure MySQL service is healthy; run `make ps-local` and `make verify-seed`
  - For remote DB: confirm host/port are reachable from the container

- Missing `.env` or variables
  - Copy `.env.example` to `.env` and set `APP_DB_USERNAME`, `APP_DB_PASSWORD`, `MYSQL_ROOT_PASSWORD`, `APP_SECRET`
  - Run `make preflight-local` or `make preflight-remote` to validate

- Twig or YAML errors
  - Lint templates: `php bin/console lint:twig templates`
  - Lint config: `php bin/console lint:yaml config`

- Tests fail with missing tables (SQLite)
  - Create schema: `APP_ENV=test php bin/console doctrine:schema:create -q`

- Port already in use (8080)
  - Edit `docker-compose.local.yml` port mapping to a free port, e.g. `"8081:80"`

- Composer script errors during Docker build
  - Dockerfile runs vendor install with `--no-scripts` to avoid `bin/console` calls before sources are copied
