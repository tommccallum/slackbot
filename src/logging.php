<?php

function savelog($obj) {
    if ( !defined("LOG_LEVEL") ) return;
    if ( !defined("LOG_PATH") ) return;
    
    $logPath = LOG_PATH;
    $logLevel = LOG_LEVEL;

    if ( file_exists($logPath) === false ) return;
    if ( $logLevel == 0 ) return;

    $logPrefix = date("Y-m-d");
    $logPrefix .= "T";
    $logPrefix .= date("H:i:se");
    $logPrefix .= " INFO ";

    if ( gettype($obj) == "string" ) {
        $str = $obj;
    } else if ( gettype($obj) == "array" ) {
        $str = json_encode($obj);
    } else if ( is_a($obj, "Exception" ) ) {
        $str = "exception thrown: " . $obj->getMessage() . " (" . $obj->getCode() . ")";
    }
    file_put_contents($logPath, $logPrefix.$str."\n", FILE_APPEND);
}