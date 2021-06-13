<?php

spl_autoload_register(function ($class_name) {
    include $class_name . '.php';
});

require_once("helloworld.php");
require_once("createNewBot.php");
require_once("SlackIO.php");
