<?php

$str = $argv[1];
print("Looking for learning outcome that might be useful for:\n");
print($str."\n");

require_once(__DIR__."/../responder/autoload.php");
$modelPath = __DIR__."/../models/learning_outcome_model.json";
$data = array_map("str_getcsv", file(__DIR__."/../responder/data/learning_outcome_details.csv"));

$classifier = new DocumentClassifier();
$classifier->loadModel($modelPath);
$result = $classifier->classify($str, true);
$counter = 0;
if ($result == null) {
    print("No results\n");
} else {
    foreach ($result as $url => $score) {
        foreach ($data as $row) {
            if ($row[6] == $url) {
                $loText = $row[7];
            }
        }
        if ($score > 0) {
            print("$score: $url $loText\n");
            $counter++;
        }
        if ($counter > 4) {
            break;
        }
    }
}
