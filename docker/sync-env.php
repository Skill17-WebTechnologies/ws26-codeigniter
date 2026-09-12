<?php
/**
 * Fold injected environment variables into .env.
 *
 * CodeIgniter's env() helper reads $_ENV before it falls back to the real
 * environment, and its DotEnv loader copies every key it finds in .env into
 * $_ENV — including empty ones. So an empty `database.default.hostname =` in the
 * .env this container ships SHADOWS a hostname injected by docker-compose or
 * Kubernetes, and the app reports "not configured" while the variable is right
 * there in its environment.
 *
 * Writing the injected values into .env before anything reads it settles the
 * order: an explicitly injected variable beats the file it is written into.
 *
 * Run by docker-entrypoint.sh, after .env.prod has been copied over .env.
 */

$keys = [
    'CI_ENVIRONMENT',
    'app.baseURL',
    'database.default.hostname',
    'database.default.port',
    'database.default.database',
    'database.default.username',
    'database.default.password',
    'database.default.DBDriver',
    'database.default.DBPrefix',
];

$path  = __DIR__ . '/../.env';
$lines = is_file($path) ? file($path, FILE_IGNORE_NEW_LINES) : [];

foreach ($keys as $key) {
    $value = getenv($key);
    if ($value === false) {
        continue; // not injected — leave whatever .env already says
    }

    $line  = $key . ' = ' . $value;
    $found = false;

    foreach ($lines as $i => $existing) {
        // Matches both "key = value" and "key=value"; CodeIgniter writes the
        // spaced form, but either is valid in a .env.
        if (preg_match('/^\s*' . preg_quote($key, '/') . '\s*=/', $existing) === 1) {
            $lines[$i] = $line;
            $found     = true;
            break;
        }
    }

    if (! $found) {
        $lines[] = $line;
    }
}

file_put_contents($path, implode(PHP_EOL, $lines) . PHP_EOL);
