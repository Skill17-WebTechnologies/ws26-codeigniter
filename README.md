# CodeIgniter 4.6.5 — WSC2026

A real **CodeIgniter 4.6.5** application (WorldSkills 2026 Web Technologies, TP17) backed by
**MySQL** — the same engine the deployed app uses, so what works locally works there. On start
it applies the migrations, then serves.

## Run it

```bash
cp .env.example .env
docker compose up --build
```

Then open **http://localhost**. `docker compose` starts a MySQL server alongside the app, using
the credentials from your `.env`. Stop with `docker compose down`.

## Checking the connection

```bash
curl -fsS http://localhost/api/db-check
```

```json
{ "ok": true, "driver": "mysql", "host": "db", "port": 3306, "database": "app",
  "user": "app", "server_version": "8.4.11", "latency_ms": 1,
  "demo_table": "ci_visitors present" }
```

It returns **503** when the connection fails, naming the host, database and user it tried plus
the driver's message — "Access denied" for wrong credentials, "connection refused" for a wrong
host. The password is never in the response. Every WSC2026 template answers the same check, so
one command works whatever stack you chose.

The endpoint is `app/Controllers/DbCheck.php`, routed in `app/Config/Routes.php`.

## Configuration

The database connection lives in two files and **nowhere else** — not in
`app/Config/Database.php`, the `Dockerfile` or `docker-compose.yml`:

| File | Used for | In git? |
|------|----------|---------|
| `.env` | your local development database | No — gitignored |
| `.env.prod` | the deployed app; the platform fills in your credentials | Yes |

The entrypoint copies **`.env.prod`** over `.env` on every start, so a deployed container always
runs the deployed configuration. `docker compose` passes your local values as environment
variables instead, so local development never edits the deployment's file.

### Injected environment variables

CodeIgniter's `env()` helper reads `$_ENV` before the real environment, and its DotEnv loader
copies every key from `.env` into `$_ENV` — **including empty ones**. An empty
`database.default.hostname =` in the shipped `.env` therefore shadows a hostname injected by
docker-compose or Kubernetes, and the app reports "not configured" while the value sits in its
environment.

`docker/sync-env.php`, run by the entrypoint, writes any injected `CI_ENVIRONMENT`/`app.*`/
`database.default.*` variables into `.env` first, so injected values win.

## Develop

The simplest loop is Docker: edit the source, then rebuild:

```bash
cp .env.example .env
docker compose up --build
```

Edit **app/Controllers/ and app/Views/** to change routes, controllers and views.

To run it natively instead you need **PHP 8.3** (with `mysqli`), **Composer 2.9.5** and a MySQL
server of your own. Then:

```bash
cp .env.example .env    # point database.default.hostname at your server
composer install
php spark migrate --all
php spark serve
```

## The database is shared

Every project you create points at the **same** MySQL database, so it already contains other
projects' tables. This template's demo table is called **`ci_visitors`**, not `visitors`, so it
cannot collide. Keep a prefix of your own per project — `app/Config/Database.php` also has a
`DBPrefix` setting that applies one to every table at once.

## Stack

- PHP 8.3 / Composer 2.9.5 (`mysqli` + `pdo_mysql`; `pdo_sqlite` also available)
- CodeIgniter 4.6.5
- MySQL 8.4 (started by `docker compose`)
