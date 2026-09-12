#!/usr/bin/env bash
set -e
cd /app
[ -f .env ] || cp env .env

# SQLite only — no external database server. CodeIgniter's SQLite3 driver stores the
# database as a file under writable/.
#
# The path must NOT contain a directory separator. system/Database/SQLite3/Connection.php
# prepends WRITEPATH only when the configured value has no separator in it; give it
# "writable/database.db" and it is instead resolved against the current working
# directory, which is not the project root, and every connection fails with
# "SQLite3 error: Unable to open database: unable to open database file".
if ! grep -q '^database.default.DBDriver' .env; then
  cat >> .env <<'EOL'

CI_ENVIRONMENT = development
database.default.DBDriver = SQLite3
database.default.database = database.db
EOL
fi

mkdir -p writable
php spark migrate --all -n || true
exec php spark serve --host 0.0.0.0 --port 80
