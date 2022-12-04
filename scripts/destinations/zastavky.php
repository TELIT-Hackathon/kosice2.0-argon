<?php

$conn = require_once(__DIR__ . "/../connection.php");

$zastavky = [];

$st = $conn->query("SELECT * FROM zastavky");
$result = $st->fetchAll();

foreach($result as $row) {
    $zastavky[] = $row;
}
return $zastavky;
