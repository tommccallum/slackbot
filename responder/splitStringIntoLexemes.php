<?php

function splitStringIntoLexemes($message)
{
    // EMOJI are ::[A-Za-z0-9_]::
    // Learning outcomes
    // Slack users U\w{10}

    // TODO add in variables so we can use this with internal strings as well
    preg_match_all('/(::[A-Za-z0-9_]+::|[Ll][Oo]\s*\d\.\d\.\d\.\d|\d.\d.\d.\d|[Uu]\w{10}|\p{L}+\'t|[\p{L}\-]+|\p{N}[\.\p{N}]+|\.\p{N}+|\p{P}+|\S)/', $message, $words);
    return ($words[1]);
}
