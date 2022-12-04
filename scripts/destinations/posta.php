<?php
header('Access-Control-Allow-Origin: *');

if(!isset($dbh))$conn = require_once(__DIR__ . "/../connection.php");

$posta = [];

$st = $conn->query("SELECT * FROM posta");
$result = $st->fetchAll();

foreach($result as $row) {
    $posta[] = $row;
}
return $posta;
