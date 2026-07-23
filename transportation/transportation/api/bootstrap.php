<?php
/**
 * Shared API bootstrap.
 * Provides a generic CRUD handler used by every resource endpoint.
 */

require_once __DIR__ . '/../config.php';

handle_cors();

/**
 * Generic CRUD controller for a table.
 *
 * @param string $table   Database table name
 * @param array  $fields  Allowed column => sanitizer callback
 * @param string $idCol   Primary key column (default 'id')
 */
function crud_handler(string $table, array $fields, string $idCol = 'id'): void
{
    $pdo = db_connect();
    $method = $_SERVER['REQUEST_METHOD'];
    $id = isset($_GET['id']) ? (int) $_GET['id'] : null;

    $columns = array_keys($fields);

    switch ($method) {
        case 'GET':
            if ($id !== null) {
                $stmt = $pdo->prepare("SELECT * FROM {$table} WHERE {$idCol} = :id");
                $stmt->execute(['id' => $id]);
                $row = $stmt->fetch();
                if (!$row) {
                    json_error('Not found', 404);
                }
                json_response($row);
            }

            $where = [];
            $params = [];
            // Optional date-range filter: ?date_col=date&from=YYYY-MM-DD&to=YYYY-MM-DD
            $dateCol = $_GET['date_col'] ?? '';
            $from = $_GET['from'] ?? '';
            $to = $_GET['to'] ?? '';
            if ($dateCol !== '' && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $dateCol)) {
                if ($from !== '') {
                    $where[] = "{$dateCol} >= :from";
                    $params['from'] = $from;
                }
                if ($to !== '') {
                    $where[] = "{$dateCol} <= :to";
                    $params['to'] = $to;
                }
            }
            $sql = "SELECT * FROM {$table}";
            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }
            $sql .= " ORDER BY {$idCol} ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            json_response($stmt->fetchAll());
            break;

        case 'POST':
            $body = request_json();
            $payload = build_payload($fields, $body);
            if (empty($payload)) {
                json_error('No valid fields provided', 422);
            }
            $cols = array_keys($payload);
            $placeholders = array_map(fn($c) => ":{$c}", $cols);
            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES (%s) RETURNING *',
                $table,
                implode(', ', $cols),
                implode(', ', $placeholders)
            );
            $stmt = $pdo->prepare($sql);
            $stmt->execute($payload);
            json_response($stmt->fetch(), 201);
            break;

        case 'PUT':
        case 'PATCH':
            if ($id === null) {
                json_error('Missing id', 400);
            }
            $body = request_json();
            $payload = build_payload($fields, $body);
            if (empty($payload)) {
                json_error('No valid fields provided', 422);
            }
            $sets = array_map(fn($c) => "{$c} = :{$c}", array_keys($payload));
            $payload['id'] = $id;
            $sql = sprintf(
                'UPDATE %s SET %s WHERE %s = :id RETURNING *',
                $table,
                implode(', ', $sets),
                $idCol
            );
            $stmt = $pdo->prepare($sql);
            $stmt->execute($payload);
            $row = $stmt->fetch();
            if (!$row) {
                json_error('Not found', 404);
            }
            json_response($row);
            break;

        case 'DELETE':
            if ($id === null) {
                json_error('Missing id', 400);
            }
            $stmt = $pdo->prepare("DELETE FROM {$table} WHERE {$idCol} = :id RETURNING *");
            $stmt->execute(['id' => $id]);
            $row = $stmt->fetch();
            if (!$row) {
                json_error('Not found', 404);
            }
            json_response(['deleted' => true, 'id' => $row[$idCol]]);
            break;

        default:
            json_error('Method not allowed', 405);
    }
}

/**
 * Build an insert/update payload based on the allowed field map and request body.
 */
function build_payload(array $fields, array $body): array
{
    $payload = [];
    foreach ($fields as $col => $sanitizer) {
        if (!array_key_exists($col, $body)) {
            continue;
        }
        $value = $body[$col];
        $payload[$col] = $sanitizer === null ? $value : $sanitizer($value);
    }
    return $payload;
}

/**
 * Common string sanitizer.
 */
function as_string($v): string
{
    return clean_string($v);
}

/**
 * Common nullable string sanitizer.
 */
function as_string_or_null($v)
{
    if ($v === null || $v === '') {
        return null;
    }
    return clean_string($v);
}

/**
 * Common integer sanitizer.
 */
function as_int($v): ?int
{
    return $v === null || $v === '' ? null : (int) $v;
}

/**
 * Common numeric sanitizer.
 */
function as_numeric($v)
{
    return $v === null || $v === '' ? null : (float) $v;
}

/**
 * Hash a plaintext password using bcrypt.
 */
function as_password_hash($v): ?string
{
    if ($v === null || $v === '') {
        return null;
    }
    return password_hash(clean_string($v), PASSWORD_BCRYPT);
}
