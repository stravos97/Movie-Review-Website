# Movie-Review-Website

[![CI Pipeline](https://github.com/stravos97/Movie-Review-Website/actions/workflows/ci.yml/badge.svg)](https://github.com/stravos97/Movie-Review-Website/actions/workflows/ci.yml)

Web application allows users to register, log in/out, create, view and edit movie reviews. The information about the review includes movie title, director, list of actors,
running time, reviewer and the actual review. 

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
  - Keep `.env` generic; put developer-specific secrets in `.env.local` (ignored by Git).
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
