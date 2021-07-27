<?php

function hasVerbAndNounInArray($lexemes)
{
    $noun = false;
    $verb = false;
    foreach ($lexemes as $l) {
        if ($l['top'][0] == "N") {
            $noun = true;
        }
        if ($l['top'][0] == "V"    // verbs in general
                    || $l['top'][0] == "H"  // to have
                    || $l['top'][0] == "D"  // to do
                    || $l['top'][0] == "B"  // to be
                    ) {
            $verb = true;
        }
    }
    return $verb && $noun;
}

// Before we split the message into lexemes we need to split it into a series of
// clauses that we can identify subject, object and tell if there is a question etc.
function splitStringIntoClauses($str)
{
    // we need to work with the word
    $lexemes = splitStringIntoLexemes($str);
    $la = new LexicalAnalysis();
    $lexemes = $la->inferPartsOfSpeechArray($lexemes);
    $clauses = [];
    $clauseLexemes = [];
    foreach ($lexemes as $lexeme) {

        if ( $lexeme['text'] == "," ) {
            $clauseLexemes[] = $lexeme;

            $clause = null;
            if ( count($clauses) > 0 ) {
                if ( $clauses[count($clauses)-1]['type'] == "CLAUSE" ) {
                    // Here we have the second part of a sentence e.g. Tom has a bag of apples and oranges.
                    // The "oranges" is not a valid clause by itself so we want to join it with its predecessor.
                    $isCompleteClause = hasVerbAndNounInArray($clauseLexemes);        
                    if ( !$isCompleteClause ) {
                        $clause = array_pop($clauses);
                        $clause['lexemes'] = array_merge($clause['lexemes'], $clauseLexemes);
                    }
                }     
            }
            if ( !isset($clause) ) {
                $clause = [
                    'lexemes' => $clauseLexemes
                ];
            }
            $clause['type'] = "CLAUSE";
            $clauses[] = $clause;
            $clauseLexemes = [];
        
        } else if ($lexeme['text'] == "!") {
            $clauseLexemes[] = $lexeme;

            $clause = null;
            if ( count($clauses) > 0 ) {
                if ( $clauses[count($clauses)-1]['type'] == "CLAUSE" ) {
                    // Here we have the second part of a sentence e.g. Tom has a bag of apples and oranges.
                    // The "oranges" is not a valid clause by itself so we want to join it with its predecessor.
                    $isCompleteClause = hasVerbAndNounInArray($clauseLexemes);        
                    if ( !$isCompleteClause ) {
                        $clause = array_pop($clauses);
                        $clause['lexemes'] = array_merge($clause['lexemes'], $clauseLexemes);
                    }
                }     
            }
            if ( !isset($clause) ) {
                $clause = [
                    'lexemes' => $clauseLexemes
                ];
            }
            $clause['type'] = "EXCLAMATION";
            $clauses[] = $clause;
            $clauseLexemes = [];
        } elseif ($lexeme['text'] == ".") {
            $clauseLexemes[] = $lexeme;

            $clause = null;
            if ( count($clauses) > 0 ) {
                if ( $clauses[count($clauses)-1]['type'] == "CLAUSE" ) {
                    // Here we have the second part of a sentence e.g. Tom has a bag of apples and oranges.
                    // The "oranges" is not a valid clause by itself so we want to join it with its predecessor.
                    $isCompleteClause = hasVerbAndNounInArray($clauseLexemes);        
                    if ( !$isCompleteClause ) {
                        $clause = array_pop($clauses);
                        $clause['lexemes'] = array_merge($clause['lexemes'], $clauseLexemes);
                    }
                }     
            }
            if ( !isset($clause) ) {
                $clause = [
                    'lexemes' => $clauseLexemes
                ];
            }
            $clause['type'] = "STATEMENT";
            $clauses[] = $clause;
            $clauseLexemes = [];
        } elseif ($lexeme['text'] == "?") {
            $clauseLexemes[] = $lexeme;

            $clause = null;
            if ( count($clauses) > 0 ) {
                if ( $clauses[count($clauses)-1]['type'] == "CLAUSE" ) {
                    // Here we have the second part of a sentence e.g. Tom has a bag of apples and oranges.
                    // The "oranges" is not a valid clause by itself so we want to join it with its predecessor.
                    $isCompleteClause = hasVerbAndNounInArray($clauseLexemes);        
                    if ( !$isCompleteClause ) {
                        $clause = array_pop($clauses);
                        $clause['lexemes'] = array_merge($clause['lexemes'], $clauseLexemes);
                    }
                }     
            }
            if ( !isset($clause) ) {
                $clause = [
                    'lexemes' => $clauseLexemes
                ];
            }
            $clause['type'] = "QUESTION";
            $clauses[] = $clause;
            $clauseLexemes = [];
        } else {
            $clauseLexemes[] = $lexeme;
        }

        if ($lexeme['top'][0] == "C") { // conjunction of some kind
            // do we have a verb and a noun in our sentence?
            // if so then we will ascribe this as a clause
            // if not then we may have a list e.g. Tom and John go to the park. In
            // which case we ignore and continue
            $isCompleteClause = hasVerbAndNounInArray($clauseLexemes);
            if ($isCompleteClause) {
                $clause = [
                    'lexemes' => $clauseLexemes,
                    'type' => 'CLAUSE'
                ];
                $clauses[] = $clause;
                $clauseLexemes = [];
            } // else continue
        }
    }
    if (count($clauseLexemes) > 0) {
        $clause = null;
        if ( count($clauses) > 0 ) {
            if ( $clauses[count($clauses)-1]['type'] == "CLAUSE" ) {
                // Here we have the second part of a sentence e.g. Tom has a bag of apples and oranges.
                // The "oranges" is not a valid clause by itself so we want to join it with its predecessor.
                $isCompleteClause = hasVerbAndNounInArray($clauseLexemes);        
                if ( !$isCompleteClause ) {
                    $clause = array_pop($clauses);
                    $clause['lexemes'] = array_merge($clause['lexemes'], $clauseLexemes);
                }
            }     
        }
        if ( !isset($clause) ) {
            $clause = [
                'lexemes' => $clauseLexemes
            ];
        }
        $clause['type'] = "UNKNOWN";
        
        // the message did not end with any punctuation
        $firstLetter = strtoupper($clause['lexemes'][0]['top'][0]);
        if ($firstLetter == "M"            // modal auxillary e.g. can, would
            || $firstLetter == "B"          // to be
            || $firstLetter == "H"          // have
            || $firstLetter == "W"          // which what how
            || $firstLetter == "D"          // do, does etc
            ) {
            $clause['type'] = "QUESTION";
        } else {
            $clause['type'] = "STATEMENT";
        }

        $clauses[] = $clause;
        $clauseLexemes = [];
    }
    return $clauses;
}


function outputClausesAsString($clauses)
{
    $outputString = "";

    foreach ($clauses as $index => $clause) {
        $outputString .= "[".$index."] ".$clause['type']." {";
        foreach ($clause['lexemes'] as $l) {
            $outputString .= $l['original'] . "[" . $l['type'] . "," . $l['top'] . "," . (isset($l['value']) ? $l['value'] : null)."]\n";
        }
        $outputString . "}";
        return $outputString;
    }
}
