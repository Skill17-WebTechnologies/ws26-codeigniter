# CodeIgniter 4.6.5 — WSC2026 app (SQLite)

```bash
docker compose up --build
```

Open **http://localhost** (CodeIgniter welcome app). On start it runs a migration (creates a
`visitors` table) against a self-contained SQLite database file under `writable/`. No database
server is used. Pinned: PHP 8.3 / Composer 2.9.5, codeigniter4/framework 4.6.5.
