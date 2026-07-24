<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('inventory', [
    'name'     => 'as_string',
    'sku'      => 'as_string_or_null',
    'category' => 'as_string_or_null',
    'qty'      => 'as_int',
    'min'      => 'as_int',
    'price'    => 'as_numeric',
    'status'   => 'as_string_or_null',
]);
