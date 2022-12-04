<?php

$conn = require(__DIR__ . "/../connection.php");

// echo json_encode($conn);
if(!isset($zastavky))$zastavky = require_once(__DIR__ . "/zastavky.php");
$smery = [];
$ids = [];

foreach($zastavky as $zastavka) {
    $smery[$zastavka['zastavka_smer']] = [];
    $smery[$zastavka['zastavka_smer']][] = $zastavka;
}
$lines = [];
$st = $conn->query("select * from linky");

$result = $st->fetchAll();
foreach($result as $line) {
    $line['zastavky'] = [];
    $query_order = "select zastavka from zasspoje where linka=" . $line['id'] . " and spoje=1;";
    $st = $conn->query($query_order);
    $zastavkys = $st->fetchAll();

    $query = "select distinct id, name from zastavky_names inner join zaslinky on zastavky_names.id = zaslinky.zastavka where zaslinky.linka=" . $line['id'] . ';';
    $st = $conn->query($query);
    $res = $st->fetchAll();


    foreach($zastavkys as $zast) {
        foreach($res as $z_2) {
            // var_dump($z_2);
            // var_dump($z_2);
            if($z_2['id'] == $zast['zastavka']) {
                foreach($zastavky as $z_3) {
                    if($z_3['zastavka_nazov'] == $z_2['name']) {
                        $z_2['latitude'] = $z_3['y'];
                        $z_2['longitude'] = $z_3['x'];
                    }  
                }
                $line['zastavky'][] = $z_2;
            }
        }
    }
    $lines[] = $line;

    // var_dump($line);
    
    // $line['zastavky'] = [];
    // foreach($res as $zast) {
    //     foreach($zastavky as $z_coords) {
    //         if($z_coords['zastavka_nazov'] == $zast['name']) {
    //             $zast['latitude'] = $z_coords['y'];
    //             $zast['longitude'] = $z_coords['x'];
    //         }
    //     }
    //     $line['zastavky'][] = $zast;
    // }
    // $lines[] = $line;
}

return $lines;