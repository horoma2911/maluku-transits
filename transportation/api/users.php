<?php
require_once __DIR__ . '/bootstrap.php';

crud_handler('users', [
    'name'        => 'as_string',
    'email'       => 'as_string_or_null',
    'role'        => 'as_string_or_null',
    'status'      => 'as_string_or_null',
    'password'    => 'as_password_hash',
    'last_login'  => 'as_string_or_null',
]);
