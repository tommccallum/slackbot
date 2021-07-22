<?php


spl_autoload_register(function ($className) {
    $locations = [
        __DIR__,
        __DIR__."/bots",
        __DIR__."/../common",
        __DIR__."/../common/bots",
    ];

    foreach( $locations as $location ) {
        $phpFilePath = $location . "/class_" . $className . '.php';
        if ( !class_exists($className) && file_exists($phpFilePath) ) {
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