<?php

# Load all bits of dialogue and intents in this function which will attach them to the Bot class

function loadDialogue($bot)
{
    
}

function loadIntents(&$bot) 
{
    $intent_files = getDirContents(__DIR__."/intents");
    foreach( $intent_files as $intent_file ) {
        $intent = new Intent();
        $intent->loadFromFile($intent_file);
        $bot->addIntent($intent);
    }

    $bot->setPartOfDay(new PartOfDay());
}

function loadAntonyms() 
{
    $antonyms = array_map("str_getcsv", file(__DIR__ . DIRECTORY_SEPARATOR . "data/special_antonyms.txt"));
    # maps negative -> positive
    $antonym_neg_to_pos_map = [];
    foreach( $antonyms as $row ) {
        $antonym_neg_to_pos_map[$row[1]] = $row[0];
    }
    return $antonym_neg_to_pos_map;
}