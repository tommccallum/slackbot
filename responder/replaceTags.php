<?php

function replaceTags($str, $keyvalues) 
{
    if (isset($keyvalues['timestamp'])) {
        $str = preg_replace("/%dayofweek%/", date("l", $keyvalues['timestamp']), $str);
        $str = preg_replace("/%date%/", date("l jS F", $keyvalues['timestamp']), $str);
        $str = preg_replace("/%time%/", date("H:i", $keyvalues['timestamp']), $str);
    }
    if (isset($keyvalues['user'])) {
        $nameParts = explode(' ', $keyvalues['user']['real_name']);
        $str = preg_replace("/%name%/", $nameParts[0], $str);
        $str = preg_replace("/%firstname%/", $nameParts[0], $str);
        $str = preg_replace("/%surname%/", $nameParts[count($nameParts)-1], $str);
    }
    if (isset($keyvalues['me']) ) {
        // TODO replace %me.name% with name etc
        $hasMatches = preg_match_all("/%me\.(\w+)%/", $str, $matches);
        var_dump($matches);
        if ( $hasMatches ) {
            $full_text_that_matched_array = $matches[0];
            $text_that_matched_array = $matches[1];
            for($ii=0; $ii < count($full_text_that_matched_array); $ii++ ) {
                $replacement = $keyvalues['me']->get($text_that_matched_array);
                $str = preg_replace("/".$full_text_that_matched_array[$ii]."/", $replacement, $str);
            }
        }
    }
    return $str;
}