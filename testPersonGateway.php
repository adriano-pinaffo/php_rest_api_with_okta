<?php
require 'vendor/autoload.php';
use Dotenv\Dotenv;
use Src\System\DatabaseConnector;
use Src\TableGateways\PersonGateway;

$dotenv = new DotEnv(__DIR__);
$dotenv->load();

$dbConnection = (new DatabaseConnector())->getConnection();
$personGateway = new PersonGateway($dbConnection);

echo "==== Find All ====\n";
$result = $personGateway->findAll();
foreach ($result[0] as $menu => $ignore) {
  echo "$menu, ";
}
echo "\n";

foreach ($result as $index => $row) {
  foreach ($row as $key => $item) {
    echo "$item ,";
  }
  echo "\n";
}
echo "\n";

echo "==== Find ID 6 ====\n";
$result = $personGateway->find(6);
foreach ($result[0] as $menu => $ignore) {
  echo "$menu, ";
}
echo "\n";

foreach ($result as $index => $row) {
  foreach ($row as $key => $item) {
    echo "$item ,";
  }
  echo "\n";
}
echo "\n";

echo "==== Insert ====\n";
$result = $personGateway->insert([
  'firstname' => 'John',
  'lastname' => 'Smith',
  'firstparent_id' => 4,
  'secondparent_id' => null
]);
echo "Last ID inserted: $result";
echo "\n\n";

echo "==== Update ID 10 ====\n";
$result = $personGateway->update(10, [
  'firstname' => 'Joanne',
  'lastname' => 'Smithensen',
  'firstparent_id' => 4,
  'secondparent_id' => null
]);
echo "Updated $result rows";
echo "\n\n";

echo "==== Delete ID 11 ====\n";
$result = $personGateway->delete(11);
echo "Deleted $result rows";
echo "\n";
?>
