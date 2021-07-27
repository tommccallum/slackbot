<?php

function loadLearningOutcomeStrings()
{
    static $learningOutcomeStrings = null;
    if ($learningOutcomeStrings == null) {
        $learningOutcomeStrings = file(__DIR__."/data/learning_outcome_values.txt");
        $learningOutcomeStrings = array_map("strtolower", $learningOutcomeStrings);
    }
    return ($learningOutcomeStrings);
}

function isLearningOutcome($word)
{
    $learningOutcomeStrings = loadLearningOutcomeStrings();
    $lc_word = strtolower($word);
    return in_array($lc_word, $learningOutcomeStrings);
}

function formatLearningOutcome($loText)
{
    // try and automatically highlight the verbs and conjuncations?
    $loWords = splitStringIntoLexemes($loText);
    $la = new LexicalAnalysis();
    $parts = $la->inferPartsOfSpeechArray($loWords);

    $loFormattedText = "";
    foreach ($parts as $part) {
        $space = " ";
        if (substr($loFormattedText, -1, 1) == "/") {
            $space = "";
        }
        if ($part['top'][0] == "V") {
            $loFormattedText .= $space."_".$part['original']."_";
        } elseif ($part['top'][0] == "C") {
            $loFormattedText .= $space."*".$part['original']."*";
        } else {
            if (preg_match("/^\p{P}$/", $part['original'])) {
                $loFormattedText .= $part['original'];
            } else {
                $loFormattedText .= $space.$part['original'];
            }
        }
    }

    return $loFormattedText;
}
