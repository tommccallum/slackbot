<?php

function standardCapitalisation($w)
{
    return ucfirst(strtolower($w));
}

function replaceTags($str, $keyvalues)
{
    if (isset($keyvalues['timestamp'])) {
        $str = preg_replace("/%dayofweek%/", date("l", $keyvalues['timestamp']), $str);
        $str = preg_replace("/%date%/", date("l jS F", $keyvalues['timestamp']), $str);
        $str = preg_replace("/%time%/", date("H:i", $keyvalues['timestamp']), $str);
    }
    if (isset($keyvalues['user'])) {
        $nameParts = explode(' ', $keyvalues['user']['real_name']);
        $firstname = standardCapitalisation($nameParts[0]);
        $surname = standardCapitalisation($nameParts[count($nameParts)-1]);
        $str = preg_replace("/%name%/", $firstname, $str);
        $str = preg_replace("/%firstname%/", $firstname, $str);
        $str = preg_replace("/%you.first_name%/", $firstname, $str);
        $str = preg_replace("/%surname%/", $surname, $str);
    }
    if (isset($keyvalues['me'])) {
        // TODO replace %me.name% with name etc
        $hasMatches = preg_match_all("/%me\.(\w+)%/", $str, $matches);
        if ($hasMatches) {
            $full_text_that_matched_array = $matches[0];
            $text_that_matched_array = $matches[1];
            for ($ii=0; $ii < count($full_text_that_matched_array); $ii++) {
                $replacement = $keyvalues['me']->get($text_that_matched_array[$ii]);
                if (ctype_upper($replacement)) {
                    $replacement = standardCapitalisation($replacement);
                }
                $str = preg_replace("/".$full_text_that_matched_array[$ii]."/", $replacement, $str);
            }
        }
    }
    if (isset($keyvalues['you'])) {
        $hasMatches = preg_match_all("/%you\.(\w+)%/", $str, $matches);
        if ($hasMatches) {
            $full_text_that_matched_array = $matches[0];
            $text_that_matched_array = $matches[1];
            for ($ii=0; $ii < count($full_text_that_matched_array); $ii++) {
                $replacement = $keyvalues['you']->get($text_that_matched_array[$ii]);
                if (ctype_upper($replacement)) {
                    $replacement = standardCapitalisation($replacement);
                }
                $str = preg_replace("/".$full_text_that_matched_array[$ii]."/", $replacement, $str);
            }
        }
    }
    if (isset($keyvalues['part_of_day'])) {
        $hasMatches = preg_match_all("/%part_of_day\.(\w+)%/", $str, $matches);
        if ($hasMatches) {
            $full_text_that_matched_array = $matches[0];
            $text_that_matched_array = $matches[1];
            for ($ii=0; $ii < count($full_text_that_matched_array); $ii++) {
                $replacement = $keyvalues['part_of_day']->get($text_that_matched_array[$ii]);
                $str = preg_replace("/".$full_text_that_matched_array[$ii]."/", $replacement, $str);
            }
        }
    }
    return $str;
}
