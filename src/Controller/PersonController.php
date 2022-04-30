<?php
namespace Src\Controller;
use Src\TableGateways\PersonGateway;

class PersonController {
  private $db;
  private $requestMethod;
  private $userId;
  private $personGateway;

  public function __construct($db, $requestMethod, $userId) {
    // logger
    global $log;
    $this->log = $log;

    $this->db = $db;
    $this->requestMethod = $requestMethod;
    $this->userId = $userId;
    $this->personGateway = new PersonGateway($db);
  }

  public function processRequest() {
    switch($this->requestMethod) {
    case 'GET':
      if ($this->userId)
        $response = $this->getUser($this->userId);
      else
        $response = $this->getAllUsers();
      break;
    case 'POST':
      //$this->log->warning('del', $_POST);
      if (array_key_exists('method', $_POST)) {
        if ($_POST['method'] == 'put')
          $response = $this->updateUser($this->userId);
        elseif ($_POST['method'] == 'delete')
          $response = $this->deleteUser($this->userId);
      } else {
        $response = $this->addUser();
      }
      break;
    case 'PUT':
      $response = $this->updateUser($this->userId);
      break;
    case 'DELETE':
      $response = $this->deleteUser($this->userId);
      break;
    }

    header($response['status_code_header']);
    echo $response['body'];
  }

  private function getUser($id) {
    $result = $this->personGateway->find($id);
    if (!$result)
      return $this->notFound();

    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function getAllUsers() {
    //$this->log->warning('Ran from getAllUsers');
    $result = $this->personGateway->findAll();
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode($result);
    return $response;
  }

  private function addUser() {
    $contentType = $_SERVER['HTTP_CONTENT_TYPE'];
    //$this->log->warning('addUser', ['content' => $contentType]);
    //$input = $_POST;
    // https://www.php.net/manual/en/wrappers.php.php#wrappers.php.input
    // data to be sent as json
    // in Postman the body must be raw and JSON
    // in cURL the header is "Content-Type: application/json" and data is json format '{"value": "key"}'
    if ($contentType == 'application/json') {
      $input = (array) json_decode(file_get_contents('php://input'), TRUE);
      //$this->log->warning('input', $input);
    } elseif ($contentType == 'application/x-www-form-urlencoded') {
      $input = $_POST;
      //$this->log->warning('POST', $input);
    } else {
      $response['status_code_header'] = 'HTTP/1.1 415 Unsupported Media Type';
      $response['body'] = json_encode(['HTTP_CONTENT_TYPE' => $contentType]);
      return $response;
    }

    $input = $this->adjustInput($input);

    if (!$this->validatePerson($input))
      return $this->unprocessablePerson();

    $result = $this->personGateway->insert($input);
    $response['status_code_header'] = 'HTTP/1.1 201 Created';
    $response['body'] = json_encode(['id' => $result]);
    return $response;
  }

  private function updateUser($id) {
    $contentType = $_SERVER['HTTP_CONTENT_TYPE'];
    if ($contentType == 'application/json') {
      $input = (array) json_decode(file_get_contents('php://input'), TRUE);
    } elseif ($contentType == 'application/x-www-form-urlencoded') {
      $input = $_POST;
    } else {
      $response['status_code_header'] = 'HTTP/1.1 415 Unsupported Media Type';
      $response['body'] = json_encode(['HTTP_CONTENT_TYPE' => $contentType]);
      return $response;
    }

    $input = $this->adjustInput($input);

    $result = $this->personGateway->find($id);
    if (!$result)
      return $this->notFound();

    if (!$this->validatePerson($input))
      return $this->unprocessablePerson();

    $result = $this->personGateway->update($id, $input);
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(['rows' => $result]);
    return $response;
  }

  private function deleteUser($id) {
    $result = $this->personGateway->find($id);
    if (!$result)
      return $this->notFound();

    $result = $this->personGateway->delete($id);
    $response['status_code_header'] = 'HTTP/1.1 200 OK';
    $response['body'] = json_encode(['rows' => $result]);
    return $response;
  }

  private function notFound() {
    $response['status_code_header'] = 'HTTP/1.1 404 Not Found';
    $response['body'] = json_encode(['error' => 'Not Found']);
    return $response;
  }

  private function adjustInput($input) {
    $input['firstparent_id'] = $input['firstparent_id'] == '' ? null : (int) $input['firstparent_id'];
    $input['secondparent_id'] = $input['secondparent_id'] == '' ? null : (int) $input['secondparent_id'];
    return $input;
  }

  private function validatePerson($input) {
    if (!isset($input['firstname']))
      return false;

    if (!isset($input['lastname']))
      return false;

    if (!(is_numeric($input['firstparent_id']) || !$input['firstparent_id']))
      return false;

    if (!(is_numeric($input['secondparent_id']) || !$input['secondparent_id']))
      return false;

    return true;
  }

  private function unprocessablePerson() {
    $response['status_code_header'] = 'HTTP/1.1 422 Unprocessable Entity';
    $response['body'] = json_encode(['error' => 'Invalid input']);
    return $response;
  }
}
?>
