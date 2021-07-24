<?php

$str = $argv[1];
print("Looking for bookmark that might be useful for:\n");
print($str."\n");

require_once(__DIR__."/../responder/class_DocumentClassifier.php");
$modelPath = __DIR__."/../models/bookmarks.json";

$classifier = new DocumentClassifier();
$classifier->loadModel($modelPath);
$result = $classifier->classify( $str );
var_dump($result);

