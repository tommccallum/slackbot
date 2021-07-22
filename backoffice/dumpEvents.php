<?php

require_once __DIR__ . '/../vendor/autoload.php';

$collection = (new MongoDB\Client)->slackbot->events;

$result = $collection->find();
$result->toArray();
file_put_contents("../dbdump_events.json", json_encode($result));



