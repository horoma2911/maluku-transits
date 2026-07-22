#!/usr/bin/env bash
set -e

PORT="${PORT:-80}"

echo "Starting MALUKU LOGISTICS API on port $PORT"

# Ensure db directory exists for any SQLite fallback
mkdir -p "$(dirname "$0")/db"

if [ "${DB_DRIVER}" = "pgsql" ] || [ -n "${DATABASE_URL}" ]; then
  echo "PostgreSQL driver selected. Running install (create + seed)..."
  php install.php || {
    echo "WARNING: install.php failed. App will start but DB may be unavailable."
  }
else
  echo "No PostgreSQL env detected. Skipping install."
fi

echo "Starting PHP built-in server on 0.0.0.0:${PORT}..."
exec php -S "0.0.0.0:${PORT}" -t "$(dirname "$0")" "$(dirname "$0")/router.php"
