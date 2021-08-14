<?php

// declare(strict_types=1);
// use PHPUnit\Framework\TestCase;

// final class class_WordGraphTest extends TestCase
// {
//     public function test_wordgraph(): void
//     {
//         $file = __DIR__."/../responder/data/test_world/ideal_world.json";
//         $w = new WordGraph();
//         $w->include($file);

//         $factFile = __DIR__."/../responder/data/test_world/test_world_script.txt";
//         $contents = file_get_contents($factFile);
//         $lines = explode("\n", $contents);
//         $la = new LexicalAnalysis();
//         foreach ($lines as $line) {
//             $bag = splitStringIntoLexemes($line);
//             $la->inferPartsOfSpeechArray($bag);
//             $lexemes = $la->lexemes();

//             // first we go through and add all the nouns and proper nouns.
//             // we want to identify if its definite THE or indefinite A
//             // we can also add the number e.g two dogs


//             // we then add the adjectives (description of the nouns)

//             // we then add verbs (relationships) and prepositions
//             // e.g. "run from" is different than "run to"

//             // finally lets print out our world and see what we have captured
//         }

//         // we then want to ask a question
//         $question = "Who lives in Scotland?";
//     }
// }
