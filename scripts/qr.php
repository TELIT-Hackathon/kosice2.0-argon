<?php

$img = $_GET['url'];

if($_GET['code'] == "firstimeke") {
    copy($img, __DIR__ . '/../maps/pic_to_gcode/image.png');
    exec(__DIR__ . "/../maps/pic_to_gcode/script.o");
}