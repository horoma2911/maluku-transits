<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('customers', [
    'name'        => 'as_string',
    'email'       => 'as_string_or_null',
    'phone'       => 'as_string_or_null',
    'status'      => 'as_string_or_null',
    'total_trips' => 'as_int',
]);
