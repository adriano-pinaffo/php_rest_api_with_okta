<?php
require "../bootstrap.php";
use Src\Controller\PersonController;

// run from root folder as:
// $ php -S 127.0.0.1:8000 -t public

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if (!array_key_exists('REQUEST_URI', $_SERVER)) {
  exit("To be run from browser\n");
}
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// endpoint: person
if ($uri[1] != 'person') {
  header('HTTP/1.1 404 Not Found');
  exit(1);
}

$userId = null;
if (isset($uri[2]))
  $userId = (int) $uri[2];

$requestMethod = $_SERVER['REQUEST_METHOD'];

$controller = new PersonController($dbConnection, $requestMethod, $userId);
$controller->processRequest();
?>
