<?php
/**
 * ALL POST OFFICES ENDPOINT
 */
$zastavky = require(__DIR__ . "/destinations/posta.php");

echo json_encode($zastavky, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);