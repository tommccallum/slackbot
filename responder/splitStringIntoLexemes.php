<?php

function splitStringIntoLexemes($message)
{
    // for the most part this is by space but we want also split off
    // punctuation.

    // lexemes
    //$words = array('The', 'quick', 'brown', 'fox', 'jumps', 'over', 'the', 'lazy', 'dog', '.', 'A', 'long-term', 'contract', 'with', '``', 'zero-liability', "''", 'protection', '!', "Let's", 'think', 'it', 'over', '.');
    preg_match_all('/([Uu]\w{10}|\p{L}+\'t|\p{L}+|\p{P}+|[\p{N}\.]+|\S)/', $message, $words);
    return ( $words[1] );
}