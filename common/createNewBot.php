<?php


function createNewBot($app)
{
    // $whichBotToLoad = 0;
    // if ( $app->botSelectionName == "random" ) {
    //     $botFiles = glob("bots/class_*.php", GLOB_NOSORT);
    //     #var_dump($botFiles);
    //     $whichBotToLoad = floor(rand() / getrandmax() * count($botFiles));
    //     savelog("Selected bot: " . $botFiles[$whichBotToLoad]);
    //     require_once($botFiles[$whichBotToLoad]);
    // } else {
    //     if ( !isset($app->botSelectionName) ) {
    //         die("Unable to find bot as no botSelectionName set in App.");
    //     }
    //     $botFiles = array( "bots/class_".$app->botSelectionName."Bot" );
    // }
    // require_once($botFiles[$whichBotToLoad]);
    // preg_match("/class_(.*Bot).php/", $botFiles[$whichBotToLoad], $matches);
    // if ($GLOBALS['DEBUG']) {
    //         var_dump($matches);
    // }
    // $botType = $matches[1];
    // savelog("Creating bot of type: " . $botType);

    // use the autoload capability to just load the class.
    // random functionality is pretty useless so we will ignore that.
    $botType = $app->botSelectionName;
    return new $botType($app);
}
