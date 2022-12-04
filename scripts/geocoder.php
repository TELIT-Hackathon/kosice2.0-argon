<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
header('Access-Control-Allow-Origin: *');

// if(!isset($dbh)) $conn = require_once(__DIR__ . "/connection.php"); 
function translate($string) {
    $data = [
        'text'=> $string,
        'from'=> "uk",
        'to' => "sk"
    ];
    $url = "https://nlp-translation.p.rapidapi.com/v1/translate";

    $key = "eda607c2d9msh4cc828b5c4fd48fp1e6b5djsn7032c8371613";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "X-RapidAPI-Key: $key",
        // 'X-Apple-Store-Front: 143444,12'
    ));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = json_decode(curl_exec($ch), true);

    return $result['translated_text']['sk'];
}

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

$address = $_GET['address'];
$type = $_GET['type'];
$data = [];

if(isset($_GET['uk']) && $_GET['uk']) {
    $address = translate($address);
}

$response = [];
$type = $_GET['type'];
if(!isset($conn))$zastavky = require_once(__DIR__ . "/destinations/zastavky.php");
if(!isset($posta))$posty = require_once(__DIR__ . "/destinations/posta.php");


//Search categories
if(strcmp($type, "zastavka") == 0) {
    

    foreach($zastavky as $zast) {
        if(strcmp($zast['zastavka_nazov'], $address) == 0) {
            $response['latitude'] = $zast['y'];
            $response['longitude'] = $zast['x'];
            $response['zastavka'] = $zast;
            // break;
        }
    }
}
else if(strcmp($type, "posta") == 0) {
    // $type = "posta";

    foreach($posta as $p) {
        if(strcmp($p['organizacia_nazov'], $address) == 0) {
            $response['latitude'] = $p['y'];
            $response['longitude'] = $p['x'];
            // break;
        }
    }
}

if(!isset($response['latitude'])) {
    echo json_encode("No points found");
    exit;
}
/**
 * FIND CONNECTIONS
 */
$a = require_once(__DIR__."/ip.php");
// echo json_encode($a);

//FIND DISTANCE BETWEEN COORDINATES
$distance = distance($a,$response);

$data['a'] = $a;
$data['b'] = $response;
$data['distance'] = $distance;

if($distance<0.2) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
}

//CONNECTIONS
//     $a = [$item['x'], $item['y']];
//     return distance($a, $a);

$n_zastavka = false;
$target = false;
//the nearest bus stop
foreach($zastavky as $zastavka) {
    $b = [
        'latitude' => $zastavka['y'],
        'longitude' => $zastavka['x'],
    ];
    $distance = distance($a, $b);
    if($n_zastavka == 0 || $n_zastavka > $distance) {
        $n_zastavka = $distance;
        $target = $zastavka;
    } 
}
$data['a']['zastavka'] = $target['zastavka_nazov'];
if($type !== "zastavka") {
    $n_zastavka = 0;
    $target_zastavka = false;
    //the nearest bus stop
    foreach($zastavky as $zastavka) {
        $b = [
            'latitude' => $zastavka['y'],
            'longitude' => $zastavka['x'],
        ];
        $distance = distance($response, $b);
        if($n_zastavka == 0 || $n_zastavka > $distance) {
            $n_zastavka = $distance;
            $target_zastavka = $zastavka;
        } 
    }
} else {
    $target_zastavka = $response['zastavka'];
}
$data['b']['zastavka'] = $target_zastavka['zastavka_nazov'];

$line_1 = 0;
$line_2 = 0;
$same_line = false;
if(!isset($lines)) $linky = require_once(__DIR__ . "/destinations/lines.php");
foreach($lines as $line) {
    // var_dump($line);
    foreach($line['zastavky'] as $zastavka) {
        if(strcmp($zastavka['name'], $target['zastavka_nazov']) == 0) {
            if(!$same_line)$line_1 = $line;
            foreach($line_1['zastavky'] as $za_1) {
                if($za_1['name'] == $target_zastavka['zastavka_nazov']) {
                    $same_line = true;
                    break;
                } 
            }
        }
        // if($line_1 && strcmp($line_1['name'], $target_zastavka['zastavka_nazov']) == 0){
        //     echo $zastavka['name'];
        //     $same_line = true;
        // } 
    }
    $data['lines'] = [];
    $data['lines'][] = $line_1;
}

if(!$same_line) {
    $spojenie = 0;
        foreach($lines as $line) {
            foreach($line['zastavky'] as $zastavka) {
                if(strcmp($zastavka['name'], $target_zastavka['zastavka_nazov']) == 0) {
                    if(!$spojenie || !$line_2)$line_2 = $line;
                    if($zastavka['name'] == $target['zastavka_nazov']) {
                        $spojenie = $zastavka;
                        break;
                    } 
                }
                
            }
        } 
        
        foreach($line_1['zastavky'] as $z) {
            foreach($line_2['zastavky'] as $z_2) {
                if($z['name'] == $z_2['name']) $spojenie = $z;
            } 
        }
    $data['line_2'] = $line_2;
    $data['spojenie'] = $spojenie;
} 

$route = [];

if($same_line) {
    $complete = false;
    foreach($line_1['zastavky'] as $zast) {
        if( !$complete && !empty($route)) $route[] = $zast;
        if($zast['name'] == $target['zastavka_nazov'] || $zast['name'] == $target_zastavka['zastavka_nazov']) {
            if(empty($route))$route[] = $zast;
            else $complete = true;
        }
    }
    
    $data['lines'][0]['zastavky'] = $route;
}

echo json_encode($data, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);


$log = fopen("../log.txt", 'a');

fwrite($log, "\n");
fwrite($log, "\n".json_encode($result));
fclose($log);
