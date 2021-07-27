<?php

# This trains a new document model so that we can match a phrase to the best document in our bookmark list
# we look at both the title and the contents.

require_once("../responder/class_DocumentClassifier.php");

$data = array_map("str_getcsv", file(__DIR__."/../responder/data/learning_outcome_details.csv"));
$saveModelPath = __DIR__."/../models/learning_outcome_model.json";

$classifier = new DocumentClassifier();

foreach ($data as $index => $row) {
    if ($index > 0) { // ignore header
        $yearName = "";
        if ($row[0] == 1) {
            $yearName = "First year";
        } elseif ($row[0] == 2) {
            $yearName = "Second year";
        } elseif ($row[0] == 3) {
            $yearName = "third year";
        } elseif ($row[0] == 4) {
            $yearName = "fourth year";
        }
        $text = $yearName." ".$row['2']." ".$row['3']." ".$row['6']." ".$row['7'];
        $key = $row['6'];
        $classifier->addExample($key, $text);
    }
}

$classifier->saveModel($saveModelPath);
