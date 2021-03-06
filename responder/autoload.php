<?php

date_default_timezone_set("Europe/London");


spl_autoload_register(function ($className) {
    $locations = [
        __DIR__,
        __DIR__."/bots",
        __DIR__."/../common",
        __DIR__."/../common/bots",
    ];

    foreach ($locations as $location) {
        $phpFilePath = $location . "/class_" . $className . '.php';
        if (!class_exists($className) && file_exists($phpFilePath)) {
            require($phpFilePath);
            return;
        }
    }
});


require_once(__DIR__."/include_source.php");
include_source("logging.php");
include_source("autoload_environment.php");
include_source("helloworld.php");
include_source("createNewBot.php");
include_source("SlackIO.php");
include_source("getConversationReplies.php");
include_source("getConversationHistory.php");
include_source("isThisAlice.php");
include_source("traverseMessageBlocks.php");
include_source("splitStringIntoLexemes.php");
include_source("splitStringIntoClauses.php");
include_source("loadDialogue.php");
include_source("getDirContents.php");
include_source("class_SlackUserProfile.php");
include_source("replaceTags.php");
include_source("isLearningOutcome.php");
include_source("getBotInfo.php");
include_source("onSlackEvent.php");
autoload_environment();

// Create connection
$conn = new mysqli($GLOBALS['server'], $GLOBALS['username'], $GLOBALS['password'], $GLOBALS['db']);

// Check connection
if ($conn->connect_error) {
    die("MYSQL DB Connection failed: " . $conn->connect_error);
}

$options = ["typeMap" => ['root' => 'array', 'document' => 'array']];
$mongodb = new MongoDB\Client(null, [], $options);
