<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('purchase_orders', [
    'ref'      => 'as_string',
    'supplier' => 'as_string_or_null',
    'date'     => 'as_string_or_null',
    'amount'   => 'as_string_or_null',
    'status'   => 'as_string_or_null',
]);
