<?php
use Src\Controller\PersonController;
require "../bootstrap.php";
$dir = __DIR__ . '/../';
require __DIR__ . '/../src/Logger/logger.php';
//$log->warning('Start in index.php');

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
  exit('Not Found');
}

$userId = null;
if (isset($uri[2]))
  $userId = (int) $uri[2];

if (!authenticate()) {
  header("HTTP/1.1 401 Unauthorized");
  exit('401 Unauthorized');
}

$requestMethod = $_SERVER['REQUEST_METHOD'];

$controller = new PersonController($dbConnection, $requestMethod, $userId);
$controller->processRequest();

function authenticate() {
  try {
    switch(true) {
    case array_key_exists('HTTP_AUTHORIZATION', $_SERVER):
      $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
      break;
    case array_key_exists('Authorization', $_SERVER):
      $authHeader = $_SERVER['Authorization'];
      break;
    default;
      $authHeader = null;
      break;
    }

    preg_match('/Bearer\s(\S+)/', $authHeader, $matches);
    if (!isset($matches[1]))
      throw new \Exception('No Bearer Token');

    $jwtVerifier = (new \Okta\JwtVerifier\JwtVerifierBuilder())
      ->setIssuer(getenv('OKTAISSUER'))
      ->setAudience(getenv('OKTAAUDIENCE'))
      ->setClientId(getenv('OKTACLIENTID'))
      ->build();
    return $jwtVerifier->verify($matches[1]);
  } catch (\Exception $e) {
    return false;
  }
}
?>
