# CodeIgniter 4.6.5 — WSC2026 app (MySQL)

```bash
cp .env.example .env
docker compose up --build
```

Open **http://localhost** (CodeIgniter welcome app).
Connection check: `GET /api/db-check` — 200 when the database is reachable, 503 with the reason
when it is not.

On start the entrypoint copies `.env.prod` over `.env`, folds in any injected environment
variables (`docker/sync-env.php`), then applies the migrations — creating a `ci_visitors` table.
Every project shares one MySQL database, so the prefix keeps this app's table apart from the
competitor's other applications.

The connection is configured **only** in `.env` (local) and `.env.prod` (deployed) — never in
`app/Config/Database.php`, the Dockerfile or this compose file. Compose reads `.env` to start
MySQL *and* to configure the app, so both sides always agree.

Migrations never fail the boot: an unreachable database leaves the app serving its page instead
of crash-looping the container, and `/api/db-check` reports exactly why.

Pinned: PHP 8.3 / Composer 2.9.5, codeigniter4/framework 4.6.5, MySQL 8.4.
