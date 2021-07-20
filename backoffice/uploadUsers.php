<?php

require_once __DIR__ . '/../vendor/autoload.php';

$data = file_get_contents("../users.json");
$jsondata = json_decode($data, true);

var_dump($jsondata);

$collection = (new MongoDB\Client)->slackbot->users;

$collection->drop();

$insertManyResult = $collection->insertMany($jsondata['members']);
printf("Inserted %d document(s)\n", $insertManyResult->getInsertedCount());


