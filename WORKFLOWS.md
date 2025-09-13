# CI/CD Workflows (GitHub Actions)

This repository uses GitHub Actions for continuous integration and container image publishing. It runs fast unit tests and static checks on every push/PR, builds a Docker image on `main`, and (optionally) can perform a smoke test with Docker Compose.

## Overview

- All pushes and pull requests:
  - Install PHP dependencies and cache them
  - Prepare a SQLite test database and run PHPUnit
  - Run static checks (Composer validation, Symfony and Twig/YAML linters, Doctrine mapping validate)
- On `main` only:
  - Build a production Docker image with Docker Buildx
  - Push image to GitHub Container Registry (GHCR)
- Docker Compose smoke test job exists but is currently disabled to keep the pipeline fast. It can be re-enabled when desired.

Files:
- `.github/workflows/ci.yml` — Single workflow with multiple jobs

## Jobs

### `tests`
- Trigger: push/PR
- Environment: PHP 8.1 with SQLite extension
- Steps:
  - Composer install (no scripts) with cache
  - Create SQLite schema (`doctrine:schema:create`)
  - Run PHPUnit with `--testdox`
- Purpose: Verify application logic quickly without external services

### `checks`
- Trigger: push/PR
- Environment: PHP 8.1
- Steps:
  - Composer install (no scripts) with cache
  - Run `scripts/ci-checks.sh` which performs:
    - `composer validate --no-check-lock --strict`
    - `php -l` over `src/`
    - Symfony `about`, `debug:router`, `lint:twig`, `lint:yaml`
    - Doctrine `doctrine:schema:validate --skip-sync`
- Purpose: Catch configuration, routing, and mapping issues early

### `build`
- Trigger: push/PR; pushes only on `main`
- Needs: `tests`, `checks`
- Uses Docker Buildx with cache-from/to GHA cache
- Tags:
  - `ghcr.io/<owner>/<repo>:latest` (on `main`)
  - `ghcr.io/<owner>/<repo>:sha-<commit>`
- Notes:
  - Repository name is lowercased to satisfy GHCR requirements
  - Build args:
    - `APP_ENV=prod`
    - `WITH_XDEBUG=false` (keeps image slim)

### `compose_smoke` (disabled)
- Purpose: Launch MySQL + the built web image and poll `/health` until 200
- Current status: disabled (`if: ${{ false }}`) to reduce CI time
- What it does when enabled:
  - Generates secure random credentials via `openssl`
  - Pulls the SHA-tagged image built previously
  - Produces a temporary compose file that uses the pulled image instead of rebuilding
  - Waits for the web to become healthy; on failure, dumps container logs and `var/log/prod.log`

To re-enable: remove or set a condition on the `if: ${{ false }}` line in the `compose_smoke` job.

## Permissions

- Workflow default: `contents: read`
- Build job:
  - `packages: write` to push images to GHCR
- No elevated permissions are required for tests/checks

## Secrets and Environment

- No external secrets are required for unit tests (SQLite)
- The build job uses the default `GITHUB_TOKEN` to push images to GHCR (on `main`)
- Local/remote runs use `.env`; do not commit it. Required keys are documented in README

## Docker Build Details

- Multi-stage Dockerfile based on `php:7.4-apache`
- Vendor stage uses `php:7.4-cli` and the official Composer binary
- Composer install runs with `--no-scripts` and environment flags to prevent script execution during vendor stage
- Optional Xdebug can be enabled via `WITH_XDEBUG=true` build arg
- Runtime layer:
  - Installs minimal deps (curl for healthcheck)
  - Ensures `var/` is writable by `www-data`
  - Performs `cache:warmup` (non-fatal) to speed first request
- Build caches:
  - Composer cache via `--mount=type=cache,target=/tmp/composer-cache`
  - Apt cache via `--mount=type=cache,target=/var/cache/apt`

## Caching

- Composer dependencies are cached with `actions/cache` in tests/checks
- Docker Buildx uses GHA cache (`cache-from`/`cache-to`)
- Apt and Composer caches in Dockerfile reduce rebuild times

## Tags and Registry

- Registry: GHCR (`ghcr.io`)
- Repository: lowercased `ghcr.io/<owner>/<repo>` (e.g. `ghcr.io/stravos97/movie-review-website`)
- Tags:
  - `latest` (main branch only)
  - `sha-<commit>` on all builds

## Troubleshooting

- GHCR “repository name must be lowercase”
  - The workflow lowercases `${{ github.repository }}` before tagging
- Health endpoint returning 500 in Compose
  - Dockerfile ensures writable `var/` and health now returns JSON with graceful DB check
- Composer auto-scripts during build
  - Vendor stage uses `--no-scripts` and disables scripts via environment to avoid `bin/console` calls
- Apt lock errors during Docker build
  - Only `/var/cache/apt` is cached; `/var/lib/apt/lists` is not cached to avoid stale lock files

## Operating the System

- Development: work on branches and open PRs; tests and checks run automatically
- Merge to `main`: triggers a Docker build and push to GHCR
- Optional: re-enable the Compose smoke job for end-to-end verification

## Future Enhancements (Optional)

- Add a release workflow to tag semantic versions and publish `v*` images
- Add SBOM/attestation steps once the repository’s security model requires them
- Publish a Postman collection for quick endpoint testing

