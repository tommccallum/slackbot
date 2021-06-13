<?php

function autoload_environment() {
    if ( file_exists(".htenv.php") ) {
        require_once(".htenv.php");
    } else if ( file_exists("../.htenv.php") ) {
        require_once("../.htenv.php");
    } else {
        print("Could not locate .htenv.php file with API information in.");
        exit(0);
    }
}