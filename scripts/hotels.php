<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');

function distance($a, $b)
{
    $delta_lat = $b['latitude'] - $a['latitude'] ;
    $delta_lon = $b['longitude'] - $a['longitude'] ;

    $earth_radius = 6372.795477598;

    $alpha    = $delta_lat/2;
    $beta     = $delta_lon/2;
    $a        = sin(deg2rad($alpha)) * sin(deg2rad($alpha)) + cos(deg2rad($a['latitude'])) * cos(deg2rad($b['latitude'])) * sin(deg2rad($beta)) * sin(deg2rad($beta)) ;
    $c        = asin(min(1, sqrt($a)));
    $distance = 2*$earth_radius * $c;
    $distance = round($distance, 4);
    return $distance;
}

if(!isset($ip))$a = require_once(__DIR__."/ip.php");
$api = "eda607c2d9msh4cc828b5c4fd48fp1e6b5djsn7032c8371613";

$auth_url = "https://test.api.amadeus.com/v1/security/oauth2/token";
$hotels_url = "https://test.api.amadeus.com/v1/reference-data/locations/hotels/by-city?cityCode=ksc";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $auth_url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials&client_id=BsuGAJ1xq9TeQSufAsqc2k16ZJlVlFhe&client_secret=WDJQMM5VOz9lfz7G");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$token = json_decode(curl_exec($ch), true)['access_token'];
// echo $token;
curl_setopt($ch, CURLOPT_URL, $hotels_url);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Authorization: Bearer ' .$token,
    // 'X-Apple-Store-Front: 143444,12'
));
$hotels = json_decode(curl_exec($ch), true);

$data = [];

foreach($hotels['data'] as $hotel) {
    $b = [
        'latitude' => $hotel['geoCode']['latitude'],
        'longitude' => $hotel['geoCode']['longitude'],
    ];
    if(distance($a, $b) < 5) {
        $data[] = $hotel;
    }
}
// var_dump($hotels);
echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);