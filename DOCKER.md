# CodeIgniter 4.6.5 — WSC2026 app + MySQL 8.4

```bash
docker compose up --build
```

Open **http://localhost** (CodeIgniter welcome app). On start it waits for MySQL 8.4 and
runs a migration (creates a `visitors` table) to exercise the DB. Pinned: PHP 8.3 / Composer 2.9.5,
codeigniter4/framework 4.6.5, MySQL 8.4.
