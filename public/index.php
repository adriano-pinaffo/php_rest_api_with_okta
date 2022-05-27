<?php
use Src\Controller\PersonController;
require "../bootstrap.php";
require __DIR__ . '/../src/Logger/logger.php';
//$log->warning('SERVER', $_SERVER);

// run from root folder as:
// $ php -S 127.0.0.1:8000 -t public

const ALLOWED_ORIGIN = array('http://localhost:3000');
$method = $_SERVER['REQUEST_METHOD'];
$headers = array_filter($_SERVER, function($item) {
    return preg_match('/^HTTP_/', $item);
}, ARRAY_FILTER_USE_KEY);

if (isPreflight() && isset($_SERVER['HTTP_ORIGIN'])) {
    if (!in_array($_SERVER['HTTP_ORIGIN'], ALLOWED_ORIGIN))
        header('HTTP/1.1 401 Unauthorized');
    header('HTTP/1.1 200 OK');
    header("Access-Control-Allow-Origin: $_SERVER[HTTP_ORIGIN]");
    header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
    header('Access-Control-Allow-Headers: content-type, authorization');
    header("Access-Control-Max-Age: 3600");
    return;
}

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

    global $log;
    preg_match("/\.(.+)\./", $authHeader, $userInfo);
    if (count($userInfo) > 1)
      $clientId = json_decode(base64_decode($userInfo[1]))->cid;
    else
      return false;
    $jwtVerifier = (new \Okta\JwtVerifier\JwtVerifierBuilder())
      ->setIssuer(getenv('OKTAISSUER'))
      ->setAudience(getenv('OKTAAUDIENCE'))
      ->setClientId($clientId)
      ->build();
    return $jwtVerifier->verify($matches[1]);
  } catch (\Exception $e) {
    return false;
  }
}

function isPreflight() {
    global $method, $headers;

    if (!($method == 'OPTIONS' && isset(
        $headers['HTTP_ACCESS_CONTROL_REQUEST_METHOD'],
        $headers['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'],
        $headers['HTTP_ORIGIN'])))
        return false;
    return true;
}
?>
