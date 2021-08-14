<?php

#
# We need to dig out an amount of plain text to be made into a bag.
# We could also dig out some code and run a static analyser over it.
# We could also check emoticons as emotional indicators. (Check out Plutchik Wheel of emotions)

 function checkElementsForUserID($message)
 {
     $resultArray = walk_message_blocks($message, "getUserBlocks");
     $users = array_unique(collapseUserBlocksIntoArray($resultArray));
     return $users;
 }

function getTextBlocks($element)
{
    if ($element['type'] == "text") {
        return $element;
    }
    if ($element['type'] == "user") {
        return $element;
    }
    return null;
}

function collapseTextBlocksIntoString($blocks)
{
    $str = "";
    foreach ($blocks as $block) {
        if (isset($block['text'])) {
            $text = $block['text'];
            $str .= " ".$text;
        } elseif (isset($block['user_id'])) {
            $text = $block['user_id'];
            $str .= " ".$text;
        }
    }
    #$str = preg_replace("/\s+/"," ", $str);             # remove multiple spaces
    #$str = preg_replace("/\.\s*\./", ".", $str);        # full stops with nothing in between
    return $str;
}

function getEmojiBlocks($element)
{
    if ($element['type'] == "emoji") {
        return $element;
    }
    return null;
}

function collapseEmojiBlocksIntoArray($blocks)
{
    $result = [];
    foreach ($blocks as $block) {
        $text = $block['name'];
        $result[] = $text;
    }
    return $result;
}

function getTextAndEmojiBlocks($element)
{
    if ($element['type'] == "text") {
        return $element;
    }
    if ($element['type'] == "user") {
        return $element;
    }
    if ($element['type'] == "emoji") {
        return $element;
    }
    return null;
}

function collapseTextAndEmojiBlocksIntoString($blocks)
{
    $str = "";
    foreach ($blocks as $block) {
        if (isset($block['text'])) {
            $text = $block['text'];
            $str .= " ".$text;
        } elseif (isset($block['user_id'])) {
            $text = $block['user_id'];
            $str .= " ".$text;
        } elseif (isset($block['name'])) {
            $text = $block['name'];
            $str .= " ::".$text."::";
        }
    }
    #$str = preg_replace("/\s+/"," ", $str);             # remove multiple spaces
    #$str = preg_replace("/\.\s*\./", ".", $str);        # full stops with nothing in between
    return $str;
}


function getUserBlocks($element)
{
    if ($element['type'] == "user") {
        return $element;
    }
    return null;
}

function collapseUserBlocksIntoArray($blocks)
{
    $result = [];
    foreach ($blocks as $block) {
        $text = $block['user_id'];
        $result[] = $text;
    }
    return $result;
}


function traverseMessageBlock($parent, $callback)
{
    if (!isset($parent['elements'])) {
        return [];
    }
    if (isset($parent['type'])) {
        ## TODO How do we take into account lists and code pieces (rich_text_list and rich_text_preformatted)?
        if ($parent['type'] !== "rich_text" &&
            $parent['type'] !== "rich_text_section") {
            return [];
        }
    }
    $resultArray = [];
    foreach ($parent['elements'] as $element) {
        $resultArray = array_merge($resultArray, traverseMessageBlock($element, $callback));
        $something = $callback($element);
        if ($something !== null) {
            $resultArray[] = $something;
        }
    }
    return $resultArray;
}


/**
 * Look for any "mentions" of Alice in the entire thread
 *
 * @param [type] $conversation
 * @return void
 */
function walk_message_blocks($message, $callback)
{
    if (isset($message['event'])) {
        $message = $message['event'];
    }
    // var_dump($message);
    $resultArray = [];
    if (isset($message['blocks'])) {
        // we have been passed a single event block from say an App class
        $blocks = $message['blocks'];
        foreach ($blocks as $block) {
            $resultArray = array_merge($resultArray, traverseMessageBlock($block, $callback));
        }
    }
    return $resultArray;
}

function walk_over_conversation($conversation, $callback)
{
    $resultArray = [];
    if (isset($conversation['messages'])) {
        foreach ($conversation['messages'] as $message) {
            $messageBlockElementsArray = walk_message_blocks($message, $callback);
            $resultArray[] = $messageBlockElementsArray;
        }
    }
    return $resultArray;
}
