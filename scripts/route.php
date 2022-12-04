<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');


$zastavky = require_once(__DIR__ . "/destinations/zastavky.php");
$posta = require_once(__DIR__ . "/destinations/posta.php"); 

$a = require_once(__DIR__."ip.php");
$b = $_POST['coords'];

$type = $_POST['type'];


echo json_encode($zastavky, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);