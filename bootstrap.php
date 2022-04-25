<?php
require 'vendor/autoload.php';
use Dotenv\Dotenv;
use Src\System\DatabaseConnector;

$dotenv = new DotEnv(__DIR__);
$dotenv->load();

// run with $php bootstrap.php
//echo getenv('OKTAAUDIENCE');

$dbConnection = (new DatabaseConnector())->getConnection();
//print_r($dbConnection);
?>
