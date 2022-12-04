<?php
/**
 * CREATE CONNECTION TO DB
 */

$host = "localhost";
$db = "firstimeke";
$user = "ftke";
$pass = "ftke";

$dbh = new PDO('mysql:host=' . $host . ';dbname=' . $db, $user, $pass);

$dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

return $dbh;