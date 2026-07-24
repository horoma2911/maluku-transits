<?php
/**
 * Report export endpoint.
 *
 * GET /api/report?resource=invoices&format=pdf|xlsx|csv[&date_col=date][&from=YYYY-MM-DD][&to=YYYY-MM-DD]
 *
 * Streams report files for the selected resource without touching the database schema.
 */

require_once __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_error('Method not allowed', 405);
}

$user = current_user();
if ($user === null) {
    json_error('Authentication required', 401);
}

define('AUTH_USER_ID', (int) $user['id']);
define('AUTH_USER_ROLE', (string) ($user['role'] ?? 'Guest'));

$resource = clean_string($_GET['resource'] ?? '');
$allowedResources = [
    'vehicles', 'trailers', 'drivers', 'trips', 'customers', 'suppliers',
    'inventory', 'purchase-orders', 'expenses', 'invoices', 'payments',
    'users', 'activities',
];
if (!in_array($resource, $allowedResources, true)) {
    json_error('Unknown resource: ' . $resource, 404);
}

$role = strtolower((string) AUTH_USER_ROLE);
if (in_array($resource, ['users', 'activities'], true) && $role !== 'administrator' && $role !== 'admin') {
    json_error('Forbidden', 403);
}

$format = strtolower(clean_string($_GET['format'] ?? 'pdf'));
if (!in_array($format, ['pdf', 'xlsx', 'excel', 'csv'], true)) {
    json_error('Unsupported report format', 400);
}

$columnsMap = [
    'vehicles'       => [['Plate', 'plate'], ['Make', 'make'], ['Model', 'model'], ['Year', 'year'], ['Capacity', 'capacity'], ['Status', 'status'], ['Driver', 'driver']],
    'trailers'       => [['Type', 'type'], ['Capacity', 'capacity'], ['Plate', 'plate'], ['Status', 'status']],
    'drivers'        => [['Name', 'name'], ['Phone', 'phone'], ['License', 'license'], ['Trips', 'trips'], ['Status', 'status']],
    'trips'          => [['Ref', 'ref'], ['Origin', 'origin'], ['Destination', 'destination'], ['Vehicle', 'vehicle'], ['Driver', 'driver'], ['Date', 'date'], ['Amount', 'amount'], ['Status', 'status']],
    'customers'      => [['Name', 'name'], ['Email', 'email'], ['Phone', 'phone'], ['Total Trips', 'total_trips'], ['Status', 'status']],
    'suppliers'      => [['Name', 'name'], ['Category', 'category'], ['Email', 'email'], ['Status', 'status']],
    'inventory'      => [['Name', 'name'], ['SKU', 'sku'], ['Category', 'category'], ['Qty', 'qty'], ['Min', 'min'], ['Price', 'price'], ['Status', 'status']],
    'purchase-orders' => [['Ref', 'ref'], ['Supplier', 'supplier'], ['Date', 'date'], ['Amount', 'amount'], ['Status', 'status']],
    'expenses'       => [['Ref', 'ref'], ['Category', 'category'], ['Amount', 'amount'], ['Date', 'date'], ['Trip', 'trip'], ['Status', 'status']],
    'invoices'       => [['Ref', 'ref'], ['Client', 'client'], ['Date', 'date'], ['Amount', 'amount'], ['Due', 'due'], ['Status', 'status']],
    'payments'       => [['Ref', 'ref'], ['Method', 'method'], ['Amount', 'amount'], ['Date', 'date'], ['Status', 'status']],
    'users'          => [['Name', 'name'], ['Email', 'email'], ['Role', 'role'], ['Status', 'status'], ['Last Login', 'last_login']],
    'activities'     => [['User', 'user'], ['Action', 'action'], ['Time', 'time']],
];

$cols = $columnsMap[$resource];
$headers = array_map(fn($c) => $c[0], $cols);
$dbCols = array_map(fn($c) => $c[1], $cols);

$dateCol = clean_string($_GET['date_col'] ?? '');
$from = clean_string($_GET['from'] ?? '');
$to = clean_string($_GET['to'] ?? '');

$pdo = db_connect();
$where = [];
$params = [];
if ($dateCol !== '' && in_array($dateCol, $dbCols, true) && ($from !== '' || $to !== '')) {
    if ($from !== '') {
        $where[] = "{$dateCol} >= :from";
        $params['from'] = $from;
    }
    if ($to !== '') {
        $where[] = "{$dateCol} <= :to";
        $params['to'] = $to;
    }
}

$safeCols = array_map(function ($col) {
    return preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $col) ? $col : 'id';
}, $dbCols);
$tableName = str_replace('-', '_', $resource);
$sql = 'SELECT ' . implode(', ', $safeCols) . ' FROM ' . $tableName;
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY id ASC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$prettyTitle = ucwords(str_replace('-', ' ', $resource)) . ' Report';
$userName = 'Admin User';
$userStmt = $pdo->prepare('SELECT name FROM users WHERE id = :id LIMIT 1');
$userStmt->execute(['id' => AUTH_USER_ID]);
$userRow = $userStmt->fetch();
if ($userRow && !empty($userRow['name'])) {
    $userName = $userRow['name'];
}

$summary = [['label' => 'Total Records', 'value' => count($rows)]];
if (in_array($resource, ['expenses', 'invoices', 'payments'], true)) {
    $total = 0.0;
    foreach ($rows as $row) {
        $total += parse_amount_frontend($row['amount'] ?? 0);
    }
    $summary[] = ['label' => 'Total Amount', 'value' => 'TZS ' . number_format((int) round($total))];
}
if ($resource === 'trips') {
    $completed = 0;
    foreach ($rows as $row) {
        if (strtolower((string) ($row['status'] ?? '')) === 'completed') {
            $completed++;
        }
    }
    $summary[] = ['label' => 'Completed Trips', 'value' => $completed . ' / ' . count($rows)];
}
if ($resource === 'vehicles') {
    $active = 0;
    foreach ($rows as $row) {
        if (strtolower((string) ($row['status'] ?? '')) === 'active') {
            $active++;
        }
    }
    $summary[] = ['label' => 'Active Vehicles', 'value' => $active . ' / ' . count($rows)];
}
if ($resource === 'drivers') {
    $active = 0;
    foreach ($rows as $row) {
        if (strtolower((string) ($row['status'] ?? '')) === 'active') {
            $active++;
        }
    }
    $summary[] = ['label' => 'Active Drivers', 'value' => $active . ' / ' . count($rows)];
}
if ($resource === 'customers') {
    $totalTrips = 0;
    foreach ($rows as $row) {
        $totalTrips += (int) ($row['total_trips'] ?? 0);
    }
    $summary[] = ['label' => 'Total Trips', 'value' => $totalTrips];
}

$filename = clean_string($_GET['filename'] ?? '');
if ($filename === '') {
    $filename = $resource . '.' . $format;
    if ($from && $to) {
        $fromParts = explode('-', $from);
        $toParts = explode('-', $to);
        if (isset($fromParts[0], $fromParts[1], $toParts[0], $toParts[1]) && $fromParts[0] === $toParts[0] && $fromParts[1] === $toParts[1]) {
            $filename = $resource . '-' . substr($from, 0, 7) . '.' . $format;
        } else {
            $filename = $resource . '-' . $from . '-to-' . $to . '.' . $format;
        }
    }
}
if ($format === 'xlsx' || $format === 'excel') {
    if (!preg_match('/\.xlsx$/i', $filename)) {
        $filename .= '.xlsx';
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<?mso-application progid="Excel.Sheet"?>';
    $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"';
    $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"';
    $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"';
    $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">';
    $xml .= '<Styles>';
    $xml .= '<Style ss:ID="Default" ss:Name="Normal"><Font ss:FontName="Calibri" ss:Size="11"/></Style>';
    $xml .= '<Style ss:ID="header"><Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#003333" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/></Style>';
    $xml .= '<Style ss:ID="total"><Font ss:FontName="Calibri" ss:Size="11" ss:Bold="1" ss:Color="#003333"/><Interior ss:Color="#E8F5E9" ss:Pattern="Solid"/></Style>';
    $xml .= '<Style ss:ID="title"><Font ss:FontName="Calibri" ss:Size="16" ss:Bold="1" ss:Color="#003333"/></Style>';
    $xml .= '</Styles>';
    $xml .= '<Worksheet ss:Name="' . str_replace('"', '&quot;', $prettyTitle) . '">';
    $xml .= '<Table>';
    $xml .= '<Column ss:Width="100"/>';
    foreach ($headers as $_) {
        $xml .= '<Column ss:Width="140"/>';
    }
    $xml .= '<Row><Cell ss:StyleID="title"><Data ss:Type="String">MALUKU LOGISTICS</Data></Cell></Row>';
    $xml .= '<Row><Cell><Data ss:Type="String">' . htmlspecialchars($prettyTitle) . '</Data></Cell></Row>';
    $xml .= '<Row><Cell><Data ss:Type="String">Period: ' . htmlspecialchars($from ?: 'All') . ' to ' . htmlspecialchars($to ?: 'All') . '</Data></Cell></Row>';
    $xml .= '<Row><Cell><Data ss:Type="String">Generated: ' . date('Y-m-d H:i') . ' by ' . htmlspecialchars($userName) . '</Data></Cell></Row>';
    $xml .= '<Row><Cell ss:MergeAcross="' . (count($headers) - 1) . '"><Data ss:Type="String"/></Cell></Row>';
    $xml .= '<Row>';
    foreach ($headers as $header) {
        $xml .= '<Cell ss:StyleID="header"><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>';
    }
    $xml .= '</Row>';

    foreach ($rows as $row) {
        $xml .= '<Row>';
        foreach ($row as $cell) {
            $value = is_array($cell) ? ($cell['value'] ?? '') : $cell;
            $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars((string) ($value ?: '')) . '</Data></Cell>';
        }
        $xml .= '</Row>';
    }

    if (!empty($summary)) {
        $xml .= '<Row><Cell ss:MergeAcross="' . (count($headers) - 1) . '"><Data ss:Type="String"/></Cell></Row>';
        $xml .= '<Row><Cell ss:StyleID="total"><Data ss:Type="String">SUMMARY</Data></Cell></Row>';
        foreach ($summary as $item) {
            $xml .= '<Row><Cell><Data ss:Type="String">' . htmlspecialchars($item['label'] ?? '') . '</Data></Cell>';
            $xml .= '<Cell><Data ss:Type="String">' . htmlspecialchars($item['value'] ?? '') . '</Data></Cell></Row>';
        }
    }

    $xml .= '</Table></Worksheet></Workbook>';
    echo $xml;
    exit;
}

if ($format === 'csv') {
    if (!preg_match('/\.csv$/i', $filename)) {
        $filename .= '.csv';
    }
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-store, no-cache, must-revalidate');

    $out = fopen('php://output', 'wb');
    fputcsv($out, $headers);
    foreach ($rows as $row) {
        $record = [];
        foreach ($safeCols as $key) {
            $record[] = $row[$key] ?? '';
        }
        fputcsv($out, $record);
    }
    fclose($out);
    exit;
}

$html = build_report_html($prettyTitle, $headers, $rows, $summary, $from, $to, $userName);

require_once __DIR__ . '/../vendor/autoload.php';
$dompdfClass = class_exists('Dompdf\\Dompdf') ? 'Dompdf\\Dompdf' : (class_exists('DOMPDF') ? 'DOMPDF' : '');
if ($dompdfClass === 'DOMPDF' && file_exists(__DIR__ . '/../vendor/dompdf/dompdf/dompdf_config.inc.php')) {
    require_once __DIR__ . '/../vendor/dompdf/dompdf/dompdf_config.inc.php';
}
if ($dompdfClass !== '') {
    $dompdf = new $dompdfClass();
    if (method_exists($dompdf, 'loadHtml')) {
        $dompdf->loadHtml($html);
    } else {
        $dompdf->load_html($html);
    }
    if (method_exists($dompdf, 'setPaper')) {
        $dompdf->setPaper('A4', in_array($resource, ['trips', 'vehicles', 'drivers', 'customers', 'purchase-orders'], true) ? 'landscape' : 'portrait');
    } else {
        $dompdf->set_paper('A4', in_array($resource, ['trips', 'vehicles', 'drivers', 'customers', 'purchase-orders'], true) ? 'landscape' : 'portrait');
    }
    $dompdf->render();
    if (!preg_match('/\.pdf$/i', $filename)) {
        $filename .= '.pdf';
    }
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo method_exists($dompdf, 'output') ? $dompdf->output() : $dompdf->stream();
    exit;
}

require_once __DIR__ . '/../lib/pdf.php';
$pdf = new PdfReport('MALUKU LOGISTICS - ' . $prettyTitle);
$pdf->setColumns($headers);
$pdf->setMeta('Generated By', $userName);
$pdf->setPeriod($from ?: null, $to ?: null);
if (in_array($resource, ['trips', 'vehicles', 'drivers', 'customers', 'purchase-orders'], true)) {
    $pdf->setOrientation('landscape');
}
foreach ($rows as $row) {
    $pdf->addRow(array_map(fn($col) => $row[$col] ?? '', $safeCols));
}
$pdf->setSummary($summary);
if (!preg_match('/\.pdf$/i', $filename)) {
    $filename .= '.pdf';
}
$pdf->output($filename);

function build_report_html(string $title, array $headers, array $rows, array $summary, string $from, string $to, string $userName): string
{
    $tableRows = '';
    foreach ($rows as $row) {
        $cells = '';
        foreach ($row as $cell) {
            $cells .= '<td>' . htmlspecialchars((string) ($cell ?: '')) . '</td>';
        }
        $tableRows .= '<tr>' . $cells . '</tr>';
    }

    $summaryRows = '';
    foreach ($summary as $item) {
        $summaryRows .= '<div style="border:1px solid #e5e7eb; border-radius:8px; padding:12px; background:#f8fafc; margin-right:10px; margin-bottom:10px; display:inline-block; min-width:180px;">';
        $summaryRows .= '<div style="font-size:11px; color:#6b7280; text-transform:uppercase;">' . htmlspecialchars($item['label']) . '</div>';
        $summaryRows .= '<div style="font-size:18px; font-weight:700; color:#003333; margin-top:4px;">' . htmlspecialchars($item['value']) . '</div>';
        $summaryRows .= '</div>';
    }

    return '<!DOCTYPE html><html><head><meta charset="utf-8"><style>body{font-family:Arial,sans-serif;color:#111827;padding:24px;} table{border-collapse:collapse;width:100%;font-size:11px;} th,td{border:1px solid #d1d5db;padding:8px;text-align:left;} th{background:#003333;color:#fff;} .title{font-size:20px;font-weight:700;color:#003333;margin-bottom:10px;} .meta{font-size:12px;color:#4b5563;margin-bottom:18px;} </style></head><body><div class="title">' . htmlspecialchars($title) . '</div><div class="meta">Period: ' . htmlspecialchars($from ?: 'All') . ' to ' . htmlspecialchars($to ?: 'All') . ' | Generated: ' . htmlspecialchars(date('Y-m-d H:i')) . ' by ' . htmlspecialchars($userName) . '</div><div style="margin-bottom:16px;">' . $summaryRows . '</div><table><thead><tr>' . implode('', array_map(fn($header) => '<th>' . htmlspecialchars($header) . '</th>', $headers)) . '</tr></thead><tbody>' . $tableRows . '</tbody></table></body></html>';
}

function parse_amount_frontend($val): float
{
    if (is_numeric($val)) {
        return (float) $val;
    }
    $n = floatval(preg_replace('/[^0-9.\-]/', '', (string) $val));
    return is_nan($n) ? 0.0 : $n;
}
