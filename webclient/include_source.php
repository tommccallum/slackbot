<?php

function include_source($path)
{
    $locations = [
        __DIR__,
        __DIR__.DIRECTORY_SEPARATOR."..".DIRECTORY_SEPARATOR."common"
    ];

    foreach ($locations as $location) {
        if (file_exists($location.DIRECTORY_SEPARATOR.$path)) {
            require_once($location.DIRECTORY_SEPARATOR.$path);
            return true;
        }
    }
    
    throw new \Exception("Could not find source file: ".$path);
}