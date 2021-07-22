<?php

#
# Replies to messages in the database
# This is NOT the same as postMessages which posts scheduled messages. 
#

require_once __DIR__ . '/../vendor/autoload.php';
$collection = (new MongoDB\Client)->slackbot->events;

$result = $collection->find(['slackbot.replied_to' => false, 'event.type' => "message"]);

foreach ($result as $msg) {
    var_dump($msg);
}
// $bot = createNewBot($app);
// $botResponseText = $bot->handle($app);
// if (isset($botResponseText)) {
//     savelog($botResponseText);
//     $bot->printInfo();
//     sendMessage($app, $botResponseText);
// }  else { // else the user is not expecting a response to this event
//     savelog("No response sent in response to this event.");
// }