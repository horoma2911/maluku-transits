<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('drivers', [
    'name'   => 'as_string',
    'phone'  => 'as_string_or_null',
    'license' => 'as_string_or_null',
    'status' => 'as_string_or_null',
    'trips'  => 'as_int',
]);
