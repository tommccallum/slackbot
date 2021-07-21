<?php

$str = "My name is %me.name% and I live in %me.country%";

$hasMatches = preg_match_all("/%me\.(\w+)/", $str, $matches);
var_dump($matches);
// if ( $hasMatches ) {
//     $full_text_that_matched = $matches[0];
//     $text_that_matched = $matches[1];
// }