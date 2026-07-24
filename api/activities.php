<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('activities', [
    'user'   => 'as_string_or_null',
    'action' => 'as_string_or_null',
    'time'   => 'as_string_or_null',
    'icon'   => 'as_string_or_null',
]);
