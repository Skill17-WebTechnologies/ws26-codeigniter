#!/usr/bin/env bash
set -e
cd /app

# The platform writes this competitor's own credentials into .env.prod. Copy it
# over .env so the deployed app reads the deployed configuration.
#
# Local development keeps its own .env, which is gitignored and never shipped —
# docker-compose.yml passes those values as environment variables instead, and
# CodeIgniter's DotEnv never overwrites a variable that is already set.
if [ -f .env.prod ]; then
  cp .env.prod .env
elif [ ! -f .env ]; then
  cp .env.example .env
fi

# See the script for why this is needed: CodeIgniter's env() prefers $_ENV, which
# its DotEnv fills from .env — so an empty value in the file shadows a variable
# injected by compose or Kubernetes unless it is written into the file first.
php docker/sync-env.php

# Never fatal: a database that is unreachable for a moment should leave the app
# serving its error page — and /api/db-check reporting exactly why — rather than
# crash-looping the container.
php spark migrate --all -n || \
  echo "migrations not applied — see /api/db-check for the reason" >&2

exec php spark serve --host 0.0.0.0 --port 80
