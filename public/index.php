<?php
error_reporting(0);
ini_set(display_errors, 0);
//error_reporting(E_ALL);
//ini_set('display_errors', '1');
define ( 'DOMAIN_ROOT', 'https://' . $_SERVER ['SERVER_NAME'] . '/s4gcomweb/public/' );
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}


require __DIR__ . '/../vendor/autoload.php';

session_start();
$dia=array("Lunes","Martes","Miercoles","Jueves","Viernes","Sabado","Domingo");
//die(var_dump($_SESSION));
// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../src/dependencies.php';

// Register middleware
require __DIR__ . '/../src/middleware.php';

// Register routes
require __DIR__ . '/../src/routes.php';

// Run app

$app->run();
