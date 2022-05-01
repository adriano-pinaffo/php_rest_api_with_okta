<?php
namespace Src\TableGateways;

class PersonGateway {
  private $db = null;

  public function __construct($db) {
    // logger
    global $log;
    $this->log = $log;

    $this->db = $db;
  }

  public function findAll() {
    $statement = "SELECT * FROM person";
    try {
      $query = $this->db->query($statement);
      // \PDO::FETCH_ASSOC returns result in an associative array
      $result = $query->fetchAll(\PDO::FETCH_ASSOC);
      return $result;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }

  public function find($id) {
    $statement = "SELECT * FROM person WHERE id = ?";
    try {
      $query = $this->db->prepare($statement);
      $query->execute(array($id));
      $result = $query->fetchAll(\PDO::FETCH_ASSOC);
      return $result;
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }

  public function insert(Array $input) {
    //$this->log->warning('insert-input', $input);
    $statement = "
    INSERT INTO person
      (firstname, lastname, firstparent_id, secondparent_id)
    VALUES
      (:firstname, :lastname, :firstparent_id, :secondparent_id);
    ";
    try {
      $result = $this->db->prepare($statement);
      $result->execute(array(
        'firstname' => $input['firstname'],
        'lastname' => $input['lastname'],
        'firstparent_id' => $input['firstparent_id'] ?? null,
        'secondparent_id' => $input['secondparent_id'] ?? null,
      ));
      //return $result->rowCount();
      return $this->db->lastInsertId();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }

  public function update($id, Array $input) {
    //$this->log->warning('update-input', $input);
    $statement = "
    UPDATE person SET
      firstname = :firstname,
      lastname = :lastname,
      firstparent_id = :firstparent_id,
      secondparent_id = :secondparent_id
    WHERE
      id = :id;
    ";
    try {
      $result = $this->db->prepare($statement);
      $result->execute(array(
        'id' => (int) $id,
        'firstname' => $input['firstname'],
        'lastname' => $input['lastname'],
        'firstparent_id' => $input['firstparent_id'] ?? null,
        'secondparent_id' => $input['secondparent_id'] ?? null,
      ));
      return $result->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }

  public function delete($id) {
    $statement = "DELETE FROM person WHERE id = ?";
    try {
      $result = $this->db->prepare($statement);
      $result->execute(array($id));
      return $result->rowCount();
    } catch (\PDOException $e) {
      exit($e->getMessage());
    }
  }
}
?>
