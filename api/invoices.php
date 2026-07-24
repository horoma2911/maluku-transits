<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('invoices', [
    'ref'     => 'as_string',
    'client'  => 'as_string',
    'date'    => 'as_string_or_null',
    'amount'  => 'as_string_or_null',
    'due'     => 'as_string_or_null',
    'status'  => 'as_string_or_null',
]);
