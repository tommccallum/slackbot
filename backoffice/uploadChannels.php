<?php

require_once __DIR__ . '/../vendor/autoload.php';

$data = file_get_contents("../channels_list.json");
$jsondata = json_decode($data, true);

var_dump($data);

$collection = (new MongoDB\Client)->slackbot->channels;

$collection->drop();

$insertManyResult = $collection->insertMany($jsondata['channels']);
printf("Inserted %d document(s)\n", $insertManyResult->getInsertedCount());


