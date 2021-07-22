<?php

function savelog($obj) {
    global $LOG_PATH;
    global $outputLogToScreen;

    if ( !defined("LOG_LEVEL") ) return;
    if ( !defined("LOG_PATH") && !isset($LOG_PATH)) return;
    if ( isset($LOG_PATH) ) {
        $logPath = $LOG_PATH;
    } else if ( defined("LOG_PATH") ) {
        $logPath = LOG_PATH;
    }
    $logLevel = LOG_LEVEL;

    if ( file_exists($logPath) === false ) return;
    if ( $logLevel == 0 ) return;

    $logPrefix = date("Y-m-d");
    $logPrefix .= "T";
    $logPrefix .= date("H:i:sT");
    
    $color = null;
    if ( gettype($obj) == "string" ) {
        $str = $obj;
        $logPrefix .= " INFO ";
    } else if ( gettype($obj) == "array" ) {
        $str = json_encode($obj);
        $logPrefix .= " INFO ";
    } else if ( is_a($obj, "Exception" ) ) {
        $color = "red";
        $str = "exception thrown: " . $obj->getMessage() . " (" . $obj->getCode() . ")";
        $logPrefix .= " ERROR ";
    }
    file_put_contents($logPath, $logPrefix.$str."\n", FILE_APPEND);

    if ( $outputLogToScreen ) {
        if (isset($color)) {
            if ($color === "red") {
                print("\033[31;1m".$logPrefix.$str."\033[0m\n");
            } else {
                print($logPrefix.$str."\n");
            }
        } else {
            print($logPrefix.$str."\n");
        }
        flush();
    }
}