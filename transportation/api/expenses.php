<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('expenses', [
    'ref'      => 'as_string',
    'category' => 'as_string_or_null',
    'amount'   => 'as_numeric',
    'date'     => 'as_string_or_null',
    'trip'     => 'as_string_or_null',
    'status'   => 'as_string_or_null',
]);
