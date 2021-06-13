<?php


function createNewBot($app)
{
    $whichBotToLoad = 0;
    if ( $app->botSelectionName == "random" ) {
        $botFiles = glob("bots/class_*.php", GLOB_NOSORT);
        var_dump($botFiles);
        $whichBotToLoad = floor(rand() / getrandmax() * count($botFiles));
        if ( $GLOBALS['DEBUG'] ) {
            print("Selected bot: " . $botFiles[$whichBotToLoad] . "\n");
        }
        require_once($botFiles[$whichBotToLoad]);
    } else {
        if ( !isset($app->botSelectionName) ) {
            die("Unable to find bot as no botSelectionName set in App.");
        }
        $botFiles = array( "bots/class_".$app->botSelectionName."Bot.php" );
    }
    require_once($botFiles[$whichBotToLoad]);
    preg_match("/class_(.*Bot).php/", $botFiles[$whichBotToLoad], $matches);
    if ($GLOBALS['DEBUG']) {
            var_dump($matches);
    }
    $botType = $matches[1];
    if ( $GLOBALS['DEBUG'] ) {
        print("Creating bot of type: " . $botType . "\n");
    }
    return new $botType($app);
}
