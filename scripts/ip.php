<?php
/**
 * GET CURRENT LOCATION
 */
$ip = $_SERVER['REMOTE_ADDR'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://ipinfo.io/".$ip."?token=77011558ab20a9");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$result = json_decode(curl_exec($ch), true);
$location = $result['loc'];
$cords = explode(',', $location);

$log = fopen("../log.txt", 'a');

fwrite($log, "\n");
fwrite($log, "\n".json_encode($cords));
fclose($log);

$res = [
    'latitude' => $cords[0],
    'longitude' => $cords[1],
    // 'latitude' => '48.731636',
    // 'longitude' => '21.244307',
];

return $res;