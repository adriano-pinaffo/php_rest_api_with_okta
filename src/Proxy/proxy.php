<?php
require __DIR__ . '/../Logger/logger.php';

const ALLOWED_ORIGIN = 'http://localhost:3000';

// Run the proxy as:
// $ php -S 127.0.0.1:8001 src/Proxy/proxy.php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];
$headers = array_filter($_SERVER, function($item) {
    return preg_match('/^HTTP_/', $item);
}, ARRAY_FILTER_USE_KEY);

if (isPreflight() && isset($_SERVER['HTTP_ORIGIN'])) {
    if ($_SERVER['HTTP_ORIGIN'] === ALLOWED_ORIGIN)
        header('HTTP/1.1 401 Unauthorized');
    header('HTTP/1.1 200 OK');
    header("Access-Control-Allow-Origin: $_SERVER[HTTP_ORIGIN]");
    header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, DELETE");
    header('Access-Control-Allow-Headers: content-type, authorization');
    header("Access-Control-Max-Age: 3600");
    return;
}
header("Access-Control-Allow-Origin: $_SERVER[HTTP_ORIGIN]");

$input = (array) json_decode(file_get_contents('php://input'), TRUE);

if (strtolower($method) != 'post') {
    header('HTTP/1.1 405 Method Not Allowed');
    return;
}

if (!$input) {
    header('HTTP/1.1 422 Unprocessable Entity');
    return;
}

$method = $input['method'];
$headers = $input['headers'];
$url = $input['url'];
$payload = $input['body'];

$token = json_encode(getToken($method, $headers, $url, $payload));

header('HTTP/1.1 200 OK');
echo $token;

function isPreflight() {
    global $method, $headers;

    if (!($method == 'OPTIONS' && isset(
        $headers['HTTP_ACCESS_CONTROL_REQUEST_METHOD'],
        $headers['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'],
        $headers['HTTP_ORIGIN'])))
        return false;
    return true;
}

function getToken($method, $headers, $url, $payload) {
    global $log;
    $data = ['method' => null, 'payload' => null, 'custom' => null];
    if (strtolower($method) == 'get') {
        $data['method'] = CURLOPT_HTTPGET;
    } else if (strtolower($method) == 'post') {
        $data['method'] = CURLOPT_POST;
        $data['payload'] = $payload;
    } else if (strtolower($method) == 'put') {
        $data['method'] = CURLOPT_PUT;
        $data['payload'] = $payload;
    } else if (strtolower($method) == 'delete') {
        $data['custom'] = $method;
        $data['payload'] = $payload;
    } else {
        header('HTTP/1.1 405 Method Not Allowed');
        return;
    }

    $headers_arr = array();
    foreach ($headers as $name=>$value)
        array_push($headers_arr, $name . ': ' . $value);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers_arr);

    if (isset($data['method']))
        curl_setopt($ch, $data['method'], true);
    if (isset($data['custom']))
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $data['custom']);
    if (isset($data['payload']))
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $result = json_decode(curl_exec($ch));
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($status < 200 || $status >= 300)
        return false;
    return $result;
}
?>
