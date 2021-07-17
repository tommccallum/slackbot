<?php

spl_autoload_register(function ($className) {
    $phpFilePath = __DIR__ . "/class_" . $className . '.php';
    if ( !class_exists($className) && file_exists($phpFilePath) ) {
        require($phpFilePath);
    } else {
        $phpFilePath = __DIR__ . "/bots/class_" . $className . '.php';
        if ( !class_exists($className) && file_exists($phpFilePath) ) {
            require($phpFilePath);
        }
    }
});

require_once("logging.php");
require_once("autoload_environment.php");
require_once("helloworld.php");
require_once("createNewBot.php");
require_once("SlackIO.php");
