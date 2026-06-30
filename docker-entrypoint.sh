#!/usr/bin/env bash
set -e
cd /app
[ -f .env ] || cp env .env
if ! grep -q '^database.default.hostname' .env; then
  cat >> .env <<'EOL'

CI_ENVIRONMENT = development
database.default.hostname = db
database.default.database = ci4
database.default.username = ci4
database.default.password = ci4
database.default.DBDriver = MySQLi
database.default.port = 3306
EOL
fi
echo "Waiting for database + running migrations..."
for i in $(seq 1 40); do
  if php spark migrate --all -n >/tmp/migrate.log 2>&1; then echo "migrations applied"; break; fi
  echo "  db not ready yet ($i)"; sleep 3
done
exec php spark serve --host 0.0.0.0 --port 8080
