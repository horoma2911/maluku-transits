<?php
/**
 * PHP built-in server router.
 * Routes /api/<resource>[/...] requests to api/index.php so the
 * front-controller can dispatch them. Everything else is served as a
 * static file (or 404).
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if (strpos($uri, '/api/') === 0 || $uri === '/api') {
    // Rewrite so the front-controller sees the correct script name.
    $_SERVER['SCRIPT_NAME'] = '/api/index.php';
    $_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/api/index.php';
    require __DIR__ . '/api/index.php';
    return true;
}

// Static file handling (default behaviour)
return false;
