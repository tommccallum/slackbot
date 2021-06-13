<?php

function log($str) {
    if ( !defined("LOG_LEVEL") ) return;
    if ( !defined("LOG_PATH") ) return;
    
    $logPath = LOG_PATH;
    $logLevel = LOG_LEVEL;

    if ( file_exists($logPath) === false ) return;
    if ( $logLevel == 0 ) return;

    $logPrefix = date("%Y-%m-%dT%H:%M:%S INFO ");

    if ( gettype($str) == "string" ) {
        file_put_contents($logPath, $logPrefix.$str, FILE_APPEND);
    } else if ( gettype($str) == "array" ) {
        $str = json_encode($str);
        file_put_contents($logPath, $logPrefix.$str, FILE_APPEND);
    }
}