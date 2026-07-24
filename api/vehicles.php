<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('vehicles', [
    'plate'    => 'as_string',
    'make'     => 'as_string',
    'model'    => 'as_string',
    'year'     => 'as_int',
    'capacity' => 'as_string_or_null',
    'status'   => 'as_string_or_null',
    'driver'   => 'as_string_or_null',
]);
