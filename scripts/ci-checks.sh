#!/usr/bin/env bash
set -euo pipefail

echo "PHP: $(php -v | head -n 1)"
echo "Composer: $(composer --version)"

echo "Validating composer.json/composer.lock"
# Ignore lock hash drift in CI and keep strict schema checks
composer validate --no-check-publish --no-check-lock --strict

echo "PHP lint"
find src -type f -name "*.php" -print0 | xargs -0 -n1 -P4 php -l > /dev/null

echo "Symfony checks"
php bin/console about -q
php bin/console debug:router -q
php bin/console lint:twig templates -q
php bin/console lint:yaml config -q

echo "Doctrine mapping validation"
php bin/console doctrine:schema:validate --skip-sync -q

echo "All checks passed."
