<?php

function loadLearningOutcomeStrings() {
    static $learningOutcomeStrings = null;
    if ($learningOutcomeStrings == null) {
        $learningOutcomeStrings = file(__DIR__."/data/learning_outcome_values.txt");
        $learningOutcomeStrings = array_map("strtolower", $learningOutcomeStrings);
    }
    return ( $learningOutcomeStrings );
}

function isLearningOutcome($word)
{
    $learningOutcomeStrings = loadLearningOutcomeStrings();
    $lc_word = strtolower($word);
    return in_array($lc_word, $learningOutcomeStrings);
}