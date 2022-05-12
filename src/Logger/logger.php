<?php
require __DIR__ . '/../../vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$log_dir = __DIR__;

$log = new Logger('person-logger');
$log->pushHandler(new StreamHandler($log_dir . '/person.log', Logger::DEBUG));

//$log->warning('testing');
?>
