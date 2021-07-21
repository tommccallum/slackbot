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

require_once(__DIR__.DIRECTORY_SEPARATOR."logging.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."autoload_environment.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."helloworld.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."createNewBot.php");
require_once(__DIR__.DIRECTORY_SEPARATOR."SlackIO.php");