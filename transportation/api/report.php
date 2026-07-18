<?php
/**
 * PDF report endpoint.
 *
 * GET /api/report?resource=invoices[&date_col=date][&from=YYYY-MM-DD][&to=YYYY-MM-DD]
 *
 * Streams a dependency-free PDF listing of the given resource, optionally
 * filtered by a date range. Requires authentication.
 */

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Method not allowed', 405);
}

// Auth (same as protected routes)
$token = verify_token(get_bearer_token());
if ($token === null) {
    json_error('Authentication required', 401);
}
define('AUTH_USER_ID', $token);

$resource = clean_string($_GET['resource'] ?? '');
$allowed = [
    'vehicles', 'trailers', 'drivers', 'trips', 'customers', 'suppliers',
    'inventory', 'purchase-orders', 'expenses', 'invoices', 'payments',
    'users', 'activities',
];
if (!in_array($resource, $allowed, true)) {
    json_error('Unknown resource: ' . $resource, 404);
}

// Column definitions per resource: [header, dbColumn]
$columnsMap = [
    'vehicles'       => [['Plate', 'plate'], ['Make', 'make'], ['Model', 'model'], ['Year', 'year'], ['Status', 'status'], ['Driver', 'driver']],
    'trailers'       => [['Type', 'type'], ['Capacity', 'capacity'], ['Plate', 'plate'], ['Status', 'status']],
    'drivers'        => [['Name', 'name'], ['Phone', 'phone'], ['License', 'license'], ['Status', 'status'], ['Trips', 'trips']],
    'trips'          => [['Ref', 'ref'], ['Origin', 'origin'], ['Destination', 'destination'], ['Vehicle', 'vehicle'], ['Driver', 'driver'], ['Date', 'date'], ['Status', 'status']],
    'customers'      => [['Name', 'name'], ['Email', 'email'], ['Phone', 'phone'], ['Status', 'status'], ['Total Trips', 'total_trips']],
    'suppliers'      => [['Name', 'name'], ['Category', 'category'], ['Email', 'email'], ['Status', 'status']],
    'inventory'      => [['Name', 'name'], ['SKU', 'sku'], ['Category', 'category'], ['Qty', 'qty'], ['Min', 'min'], ['Price', 'price'], ['Status', 'status']],
    'purchase-orders'=> [['Ref', 'ref'], ['Supplier', 'supplier'], ['Date', 'date'], ['Amount', 'amount'], ['Status', 'status']],
    'expenses'       => [['Ref', 'ref'], ['Category', 'category'], ['Amount', 'amount'], ['Date', 'date'], ['Trip', 'trip'], ['Status', 'status']],
    'invoices'       => [['Ref', 'ref'], ['Client', 'client'], ['Date', 'date'], ['Amount', 'amount'], ['Due', 'due'], ['Status', 'status']],
    'payments'       => [['Ref', 'ref'], ['Method', 'method'], ['Amount', 'amount'], ['Date', 'date'], ['Status', 'status']],
    'users'          => [['Name', 'name'], ['Email', 'email'], ['Role', 'role'], ['Status', 'status'], ['Last Login', 'last_login']],
    'activities'     => [['User', 'user'], ['Action', 'action'], ['Time', 'time']],
];

$cols = $columnsMap[$resource];
$headers = array_map(fn($c) => $c[0], $cols);
$dbCols = array_map(fn($c) => $c[1], $cols);

// Date filter
$dateCol = clean_string($_GET['date_col'] ?? '');
$from = clean_string($_GET['from'] ?? '');
$to = clean_string($_GET['to'] ?? '');

$pdo = db_connect();
$where = [];
$params = [];
if ($dateCol !== '' && in_array($dateCol, $dbCols, true) && ($from !== '' || $to !== '')) {
    if ($from !== '') { $where[] = "{$dateCol} >= :from"; $params['from'] = $from; }
    if ($to !== '')   { $where[] = "{$dateCol} <= :to";   $params['to']   = $to;   }
}

// Sanitise column list for SELECT
$safeCols = array_map(function ($c) {
    return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $c) ? $c : 'id';
}, $dbCols);
$sql = 'SELECT ' . implode(', ', $safeCols) . ' FROM ' . $resource;
if ($where) { $sql .= ' WHERE ' . implode(' AND ', $where); }
$sql .= ' ORDER BY id ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

require_once __DIR__ . '/../lib/pdf.php';

$pdf = new PdfReport('MALUKU LOGISTICS - ' . ucwords(str_replace('-', ' ', $resource)) . ' Report');
$pdf->setColumns($headers);
foreach ($rows as $r) {
    $pdf->addRow(array_map(fn($c) => $r[$c] ?? '', $safeCols));
}
$pdf->output($resource . '-report.pdf');
