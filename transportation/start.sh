#!/usr/bin/env bash
# Railway start script.
# Serves the PHP app on $PORT (Railway injects this) using PHP's built-in
# server with the API router. For PostgreSQL on Railway, set the
# DB_DRIVER, DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS env vars
# (or a DATABASE_URL) and run: php install.php
# before relying on the database.

set -e

PORT="${PORT:-80}"

echo "Starting MALUKU LOGISTICS API on port $PORT"

if [ "${DB_DRIVER}" = "pgsql" ]; then
  echo "PostgreSQL driver selected. Running install (create + seed) if needed..."
  php install.php || echo "install.php failed or already initialized (continuing)."
fi

exec php -S "0.0.0.0:${PORT}" -t "$(dirname "$0")" "$(dirname "$0")/router.php"
