<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('suppliers', [
    'name'     => 'as_string',
    'category' => 'as_string_or_null',
    'email'    => 'as_string_or_null',
    'status'   => 'as_string_or_null',
]);
