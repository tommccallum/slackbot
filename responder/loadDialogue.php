<?php

# Load all bits of dialogue and intents in this function which will attach them to the Bot class

function loadDialogue($bot)
{
    
}

function loadIntents(&$bot) {

    $intent_files = getDirContents(__DIR__."/intents");
    foreach( $intent_files as $intent_file ) {
        $contents = file_get_contents($intent_file);
        $intent = json_decode($contents, true);
        $bot->addIntent($intent);
    }

    $bot->setPartOfDay(new PartOfDay());
}