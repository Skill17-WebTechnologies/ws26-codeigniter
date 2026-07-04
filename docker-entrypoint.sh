#!/usr/bin/env bash
set -e
cd /app
[ -f .env ] || cp env .env

# SQLite only — no external database server. CodeIgniter's SQLite3 driver stores the
# database as a file under writable/.
if ! grep -q '^database.default.DBDriver' .env; then
  cat >> .env <<'EOL'

CI_ENVIRONMENT = development
database.default.DBDriver = SQLite3
database.default.database = writable/database.db
EOL
fi

mkdir -p writable
php spark migrate --all -n || true
exec php spark serve --host 0.0.0.0 --port 80
