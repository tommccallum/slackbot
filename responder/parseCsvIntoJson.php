<?php


function parseCsvIntoJson($path) 
{
    $json = [];

    $csv = array_map('str_getcsv', file($path));
    array_walk($csv, function(&$a) use ($csv) {
      $a = array_combine($csv[0], $a);
    });
    array_shift($csv); # remove column header

    foreach( $csv as $row ) {
        $item = array(
            "date" => $row['Date'],
            "time" => $row['Time'],
            "channel_name" => $row['Channel'],
            "message" => $row['Message']
        );
        $json[count($json)] = $item;
    }
    return $json;
}