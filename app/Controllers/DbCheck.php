<?php

namespace App\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;
use Throwable;

/**
 * Database connection check.
 *
 *   GET /api/db-check  → 200 {"ok":true,  ...}   connection works
 *                      → 503 {"ok":false, ...}   it does not, and why
 *
 * An expert can confirm the credentials work without reading any code:
 *
 *   curl -fsS http://localhost/api/db-check
 *
 * The 503 makes `curl -f` exit non-zero, so a whole room can be swept in one
 * loop. Every WSC2026 template answers the same check in the same shape.
 *
 * The connection itself is configured entirely in .env (local) and .env.prod
 * (deployed) — nothing is hardcoded here or in app/Config/Database.php.
 */
class DbCheck extends BaseController
{
    /** The demo table, prefixed because every project shares one database. */
    private const TABLE = 'ci_visitors';

    public function index(): ResponseInterface
    {
        $config = config(Database::class)->default;

        // Repeat the settings WITHOUT the password, so a failure says which
        // database was unreachable and a success cannot leak a credential.
        $base = [
            'driver'   => 'mysql',
            'host'     => $config['hostname'] ?: null,
            'port'     => (int) ($config['port'] ?? 3306),
            'database' => $config['database'] ?: null,
            'user'     => $config['username'] ?: null,
        ];

        // No credentials at all is almost always a .env that never arrived,
        // rather than a database that is down. Say so plainly.
        if (empty($config['hostname']) || empty($config['database'])) {
            return $this->response->setStatusCode(503)->setJSON($base + [
                'ok'    => false,
                'error' => 'database.default.hostname or database.default.database is not set',
                'hint'  => 'Local development: cp .env.example .env. Deployed: the platform writes '
                         . '.env.prod, which the entrypoint copies over .env at startup.',
            ]);
        }

        try {
            $started = microtime(true);

            // A real round trip, not just "the connection object was built".
            $db      = Database::connect();
            $version = $db->query('SELECT VERSION() AS version')->getRow()->version;
            $latency = (int) round((microtime(true) - $started) * 1000);

            // Asked separately, so the check still passes on a correctly
            // configured but not-yet-migrated database: working credentials and a
            // present schema are two different questions.
            $present = $db->tableExists(self::TABLE);

            return $this->response->setJSON($base + [
                'ok'             => true,
                'server_version' => $version,
                'latency_ms'     => $latency,
                'demo_table'     => $present ? self::TABLE . ' present' : self::TABLE . ' missing',
            ]);
        } catch (Throwable $e) {
            return $this->response->setStatusCode(503)->setJSON($base + [
                'ok'    => false,
                'error' => $e->getMessage(),
                'code'  => (string) $e->getCode(),
                'hint'  => 'Check the database.default.* values in .env (local) or .env.prod '
                         . '(deployed). Access denied means wrong credentials; connection refused '
                         . 'or a timeout means the host, port or network is wrong.',
            ]);
        }
    }
}
