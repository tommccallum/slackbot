<?php

$str = $argv[1];
print("Looking for bookmark that might be useful for:\n");
print($str."\n");

require_once(__DIR__."/../responder/autoload.php");
$modelPath = __DIR__."/../models/bookmarks.json";

$classifier = new DocumentClassifier();
$classifier->loadModel($modelPath);
$result = $classifier->classify( $str, true );
$counter = 0;
if ( $result == null ) {
    print("No results\n");
    
} else {
    foreach ($result as $url => $score) {
        if ($score > 0) {
            print("$score: $url\n");
            $counter++;
        }
        if ($counter > 4) {
            break;
        }
    }
}