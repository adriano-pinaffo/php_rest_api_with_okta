<?php
require 'bootstrap.php';

$statement = <<<EOF
  CREATE TABLE IF NOT EXISTS person(
    id INT NOT NULL AUTO_INCREMENT,
    firstname VARCHAR(100) NOT NULL,
    lastname VARCHAR(100) NOT NULL,
    firstparent_id INT DEFAULT NULL,
    secondparent_id INT DEFAULT NULL,
    PRIMARY KEY(id),
    FOREIGN KEY(firstparent_id) REFERENCES person(id) ON DELETE SET NULL,
    FOREIGN KEY(secondparent_id) REFERENCES person(id) ON DELETE SET NULL
  ) ENGINE=INNODB;

  INSERT INTO person
    (firstname, lastname, firstparent_id, secondparent_id)
  VALUES
    ('Krasimir', 'Hristozov', null, null),
    ('Maria', 'Hristozova', null, null),
    ('Masha', 'Hristozova', 1, 2),
    ('Jane', 'Smith', null, null),
    ('John', 'Smith', null, null),
    ('Richard', 'Smith', 4, 5),
    ('Donna', 'Smith', 4, 5),
    ('Josh', 'Harrelson', null, null),
    ('Anna', 'Harrelson', 7, 8);
EOF;

try {
  $dbConnection->exec($statement);
  echo "Seeded with success!\n";
} catch(\PDOException $e) {
  exit($e->getMessage());
}
?>
