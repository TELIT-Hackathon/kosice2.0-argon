<?php

/**
 * UPLOAD MAP TO MAPS FOLDER
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');


$contents = $_POST['map'] ?: json_decode(file_get_contents('php://input'))->{'map'};
$response = [];


if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    $response['status_code'] = 422;
    $response['message'] = "Only post requests!";
    echo json_encode($response);
    exit;
}
if(!$contents) {
    $response['status_code'] = 422;
    $response['message'] = "No files provided!";
    echo json_encode($response);
    exit;
}

$upload_dir = __DIR__ . "/../maps/";
$read = scandir($upload_dir);


copy($contents, __DIR__ . '/../maps/pic_to_gcode/image.png');
exec(__DIR__ . "/../maps/pic_to_gcode/script.o");

$response['status_code'] = 200;
echo json_encode($response);