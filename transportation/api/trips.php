<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('trips', [
    'ref'         => 'as_string',
    'origin'      => 'as_string',
    'destination' => 'as_string',
    'vehicle'     => 'as_string_or_null',
    'driver'      => 'as_string_or_null',
    'date'        => 'as_string_or_null',
    'status'      => 'as_string_or_null',
    'amount'      => 'as_string_or_null',
]);
