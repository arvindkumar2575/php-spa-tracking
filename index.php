<?php
// echo '<pre>';print_r($_SERVER);die;
date_default_timezone_set('Asia/Kolkata');
define('APP_START', true);
define('BASE_URL', (isset($_SERVER['HTTPS']) ? "https" : "http")."://".$_SERVER['HTTP_HOST']);
define('BASE_PATH', '/php-spa'); // change to '' in production

$routes = require __DIR__ . '/routes/route.php';

// Get clean URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (BASE_PATH !== '' && strpos($uri, BASE_PATH) === 0) {
    $uri = substr($uri, strlen(BASE_PATH));
}
$uri = rtrim($uri, '/') ?: '/';

// track request
if ($uri=='/track') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // echo 'bbbb';die;
        require __DIR__ . '/configs/tracking.php';
    } else {
        http_response_code(401);
        require __DIR__ . '/pages/401.php';
    }
    exit;
}

if (!isset($routes[$uri])) {
    http_response_code(404);
    require __DIR__ . '/pages/404.php';
    exit;
}

$page = __DIR__ . '/pages/' . $routes[$uri];

// Safety check
if (!file_exists($page)) {
    http_response_code(500);
    require __DIR__ . '/pages/500.php';
    exit;
}

require $page;
