<?php
/**
 * Database setup script.
 *
 * Usage (CLI):
 *   php install.php
 *
 * For PostgreSQL (DB_DRIVER='pgsql'): applies db/schema.sql and db/seed.sql
 * to the configured database. On Railway, the database already exists.
 *
 * For SQLite (DB_DRIVER='sqlite'): creates the SQLite file fresh,
 * then runs db/schema.sqlite.sql and db/seed.sqlite.sql.
 */

require_once __DIR__ . '/config.php';

function run_sql_file(PDO $pdo, string $path): void
{
    if (!file_exists($path)) {
        fwrite(STDERR, "Missing SQL file: {$path}\n");
        exit(1);
    }
    $sql = file_get_contents($path);
    $pdo->exec($sql);
    echo "  applied: " . basename($path) . "\n";
}

if (DB_DRIVER === 'sqlite') {
    if (file_exists(DB_SQLITE_PATH)) {
        unlink(DB_SQLITE_PATH);
        echo "Removed existing SQLite database.\n";
    }
    $db = db_connect();
    echo "Creating SQLite database '" . DB_SQLITE_PATH . "'...\n";
    echo "Applying schema...\n";
    run_sql_file($db, __DIR__ . '/db/schema.sqlite.sql');
    echo "Seeding data...\n";
    run_sql_file($db, __DIR__ . '/db/seed.sqlite.sql');
    echo "Done. SQLite setup complete.\n";
    exit;
}

// PostgreSQL path - on Railway the database already exists
$dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', DB_HOST, DB_PORT, DB_NAME);
try {
    $db = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    echo "Connected to PostgreSQL database '" . DB_NAME . "'.\n";
} catch (PDOException $e) {
    fwrite(STDERR, "ERROR: cannot connect to PostgreSQL.\n");
    fwrite(STDERR, "       " . $e->getMessage() . "\n");
    fwrite(STDERR, "       Check DB_* credentials or DATABASE_URL env var.\n");
    exit(1);
}

echo "Applying schema...\n";
run_sql_file($db, __DIR__ . '/db/schema.sql');

echo "Seeding data...\n";
run_sql_file($db, __DIR__ . '/db/seed.sql');

echo "Done. Setup complete for '" . DB_NAME . "'.\n";
