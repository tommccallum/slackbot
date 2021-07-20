<?php

require_once("sentiment_analysis.php");

// This was disappointing as it promised 0-4 categories but only had
// 0 and 4, but we can still use it.
function train_on_twitter_data(&$model) {

    $path = __DIR__ . "/../data/training.1600000.processed.noemoticon.csv";
    $csv = array_map('str_getcsv', file($path));
    // array_walk($csv, function(&$a) use ($csv) {
    //     $a = array_combine($csv[0], $a);
    // });
    array_shift($csv); # remove column header

    $NR = count($csv);
    $counter = 0;
    foreach ($csv as $row) {
        if ($counter % 1000 === 1) {
            printf("Processed %d / %d\n", $counter, $NR);
        }
        $target = $row[0];
        $text = $row[5];

        // var_dump($target);
        // var_dump($text);
        $target = intval($target);

        $model->addExample($target, $text);
        $counter++;
    }

    $model->report();
}

function getDirContents($dir, &$results = array()) {
    $files = scandir($dir);

    foreach ($files as $key => $value) {
        $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
        if (!is_dir($path)) {
            $results[] = $path;
        } else if ($value != "." && $value != "..") {
            getDirContents($path, $results);
            $results[] = $path;
        }
    }

    return $results;
}

function train_on_movie_directory($movieDirectory, &$model)
{
    $all_files_in_directory = getDirContents($movieDirectory);
    

    $NR = count($all_files_in_directory);
    $counter = 0;
    foreach ($all_files_in_directory as $path) {
        $text = file_get_contents($path);
        $info = pathinfo($path);
        $basename = $info['basename'];
        $bits = explode("_", $basename);
        $exampleIndex = $bits[0];
        $movie_rating = $bits[1]; // between 0 and 10;

        if ($counter % 1000 === 1) {
            printf("Processed %d / %d\n", $counter, $NR);
        }
        if ($movie_rating >= 9) {
            $target = 4;
        } elseif ($movie_rating >= 6) {
            $target = 3;
        } elseif ($movie_rating >= 4) {
            $target = 2;
        } elseif ($movie_rating >= 2) {
            $target = 1;
        } else {
            $target = 0;
        }

        // var_dump($target);
        // var_dump($text);
        $target = intval($target);

        $model->addExample($target, $text);
        $counter++;
    }
}
    

function train_on_movie_data(&$model) {
    $path = __DIR__ . "/../data/aclImdb/train/pos";
    train_on_movie_directory($path, $model);
    $path = __DIR__ . "/../data/aclImdb/train/neg";
    train_on_movie_directory($path, $model);
}

$model = new SentimentAnalyser();
#train_on_twitter_data($model);
train_on_movie_data($model);
$model->report();
$model->saveModel(__DIR__."/../models/sentiment_model.json");