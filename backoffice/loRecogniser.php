<?php

function isLearningOutcome($word)
{
    $values = file("../data/learning_outcome_values.txt");
    $lc_word = strtolower($word);
    $values = array_map("strtolower", $values);
    return in_array($lc_word, $values);
}