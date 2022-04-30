<?php
// Have the server running first:
// $ php -S 127.0.0.1:8000 -t public
//
// Run it like below:
//
// To get all users:
// $php public/client.php getallusers
// $php public/client.php getuser
//
// To get one user with ID 10:
// $php public/client.php getuser 10
//
// To add a user:
// $php public/client.php adduser '{"firstname": "Linus", "lastname": "Torvalds"}'
//
// To update a user:
// $ php public/client.php updateuser 5 '{"firstname": "Steve", "lastname": "Jobs", "firstparent_id": 3, "secondparent_id": 2}'
//
// To delete a user:
// $ php public/client.php deleteuser 7

if ($_SERVER['argc'] == 1) {
  echo "Not enough opitons\n";
  showHelp();
  exit(1);
}

$command = $_SERVER['argv'][1];
$ch = curl_init();
switch($command) {
case 'getallusers':
  getUser($ch);
  break;
case 'getuser':
  $id = null;
  if (array_key_exists(2, $_SERVER['argv']))
    $id = $_SERVER['argv'][2];
  getUser($ch, $id);
  break;
case 'adduser':
  $input = null;
  if (array_key_exists(2, $_SERVER['argv'])) {
    $input = $_SERVER['argv'][2];
  } else {
    echo "data not found\n";
    showHelp();
    exit(1);
  }

  addUser($ch, $input);
  break;
case 'updateuser':
  $id = null;
  if (array_key_exists(2, $_SERVER['argv'])) {
    $id = $_SERVER['argv'][2];
  } else {
    echo "id not found\n";
    showHelp();
    exit(1);
  }

  $input = null;
  if (array_key_exists(3, $_SERVER['argv'])) {
    $input = $_SERVER['argv'][3];
  } else {
    echo "data not found\n";
    showHelp();
    exit(1);
  }

  updateUser($ch, $id, $input);
  break;
case 'deleteuser':
  $id = null;
  if (array_key_exists(2, $_SERVER['argv'])) {
    $id = $_SERVER['argv'][2];
  } else {
    echo "id not found\n";
    showHelp();
    exit(1);
  }

  deleteUser($ch, $id);
  break;
}
curl_close($ch);

function getUser($ch, $id=null) {
  if (isset($id))
    echo "Getting user id $id...\n";
  else
    echo "Getting all users...\n";

  $endpoint = "http://127.0.0.1:8000/person/" . $id ?? '';
  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($ch);
  var_dump($result);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  echo "Status code: $status";
  echo "\n";
}

function addUser($ch, $input) {
  $endpoint = "http://127.0.0.1:8000/person/";
  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

  $result = curl_exec($ch);
  var_dump($result);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  echo "Status code: $status";
  echo "\n";
}

function updateUser($ch, $id, $input) {
  $endpoint = "http://127.0.0.1:8000/person/" . $id;
  $headers = array(
    "Content-Type: application/json",
    "Content-Length: " . strlen($input)
  );
  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  var_dump($result);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  echo "Status code: $status";
  echo "\n";
}

function deleteUser($ch, $id) {
  $endpoint = "http://127.0.0.1:8000/person/" . $id;

  curl_setopt($ch, CURLOPT_URL, $endpoint);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

  $result = curl_exec($ch);
  var_dump($result);
  $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  echo "Status code: $status";
  echo "\n";
}

function showHelp() {
  echo $_SERVER['argv'][0] . " command [id] [data]\n";
}
?>
