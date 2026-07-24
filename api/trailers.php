<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('trailers', [
    'type'     => 'as_string',
    'capacity' => 'as_string_or_null',
    'plate'    => 'as_string_or_null',
    'status'   => 'as_string_or_null',
]);
