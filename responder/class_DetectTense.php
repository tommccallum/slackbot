<?php

// https://leverageedu.com/blog/tenses-rule/
// This gives the most likely tense of a sentence, if there are multiple verbs then 
// these may be in different tenses but we will take the max.

# This remains slightly wrong as the Future Perfect and Future Perfect Continuous should have a 
# time frame or a duration involved as well to disambiguate them from the Future Simple and Future Continuous.
# Currently we do not look for timeframes.


class DetectTense
{
    private $text = "";
    private $words = [];
    private $analysis = null;
    private $voice = "active";

    ## the 12 different tenses ( 3x4) 
    private $past = 0;
    private $present = 0;
    private $future = 0;

    private $simple = 0;
    private $continuous = 0;
    private $perfect = 0;
    private $perfectContinuous = 0;

    public function __construct($text) {
        $this->text = strtolower($text);
        $this->words = splitStringIntoLexemes($this->text);
        $this->analysis = new LexicalAnalysis();
        $this->analysis->inferPartsOfSpeechArray($this->words);
        $this->detect();
        $this->detectVoice();
    }

    public function state() {
        return [
            'past'      => $this->past,
            'present'   => $this->present,
            'future'    => $this->future,
            'simple'    => $this->simple,
            'continuous' => $this->continuous,
            'perfect'   => $this->perfect,
            'perfectContinuous' => $this->perfectContinuous,
            "voice"     => $this->voice
        ];
    }

    public function isPast() {
        if ( $this->past > $this->present && $this->past > $this->future) {
            return true;
        }
        return false;
    }

    public function isFuture() {
        if ( $this->future > $this->past && $this->future > $this->present) {
            return true;
        }
        return false;
    }

    public function isPresent() {
        if ( $this->present > $this->past && $this->present > $this->future) {
            return true;
        }
        return false;
    }

    public function isContinuous() {
        if ( $this->continuous > $this->simple 
            && $this->continuous > $this->perfect 
            && $this->continuous > $this->perfectContinuous ) {
            return true;
        }
        return false;
    }

    public function isSimple() {
        if ( $this->simple > $this->continuous 
            && $this->simple > $this->perfect 
            && $this->simple > $this->perfectContinuous ) {
            return true;
        }
        return false;
    }

    public function isPerfect() {
        if ( $this->perfect > $this->simple 
            && $this->perfect > $this->continuous 
            && $this->perfect > $this->perfectContinuous ) {
            return true;
        }
        return false;
    }

    public function isPerfectContinuous() {
        if ( $this->perfectContinuous > $this->simple 
            && $this->perfectContinuous > $this->perfect 
            && $this->perfectContinuous > $this->continuous ) {
            return true;
        }
        return false;
    }


    private function detect() {
        // print("** DETECT ** \n");
        // var_dump($this->text);
        
        $ii =0 ;

        # reset
        $this->present = 0;
        $this->future = 0;
        $this->continuous = 0;
        $this->past = 0;
        $this->perfect = 0;
        $this->simple = 0;
        $this->perfectContinuous = 0;

        # "VB","verb, base form", (V1)
        # "VBD","verb, past tense", (V2)
        # "VBG","verb, present participle/gerund",
        # "VBN","verb, past participle", (V3)
        # "VBZ","verb, 3rd. singular present", (-s/-es)
        $verbForms = ["VB", "VBD", "VBG", "VBN", "VBZ" ];
        
        while ($ii < count($this->words)) {
            $w = $this->words[$ii];
            $thisWordAnalysis = $this->analysis->get($w);
            $firstTwoLettersOfType = strtoupper(substr($thisWordAnalysis['top'], 0, 2));
            if ( $firstTwoLettersOfType !== "BE" && 
                $firstTwoLettersOfType !== "VB" && 
                $firstTwoLettersOfType !== "HV" && 
                $firstTwoLettersOfType !== "MD"
                ) {
                $ii++;
                continue;
            }
            if ($ii < count($this->words)-1) {
                $nextWord = $this->words[$ii+1];
            } else {
                $nextWord = null;
            }
            if ($ii < count($this->words)-2) {
                $nextNextWord = $this->words[$ii+2];
            } else {
                $nextNextWord = null;
            }
            
            # find the verb which is next
            $type = null;
            $vbIndex = $ii;
            $found = false;
            for (;$vbIndex < count($this->words); $vbIndex++) {
                $lexeme = $this->analysis->get($vbIndex);
                $type = $lexeme['top'];
                $pos = strpos($type, "-");
                if ($pos !== false) {
                    $type = substr($type, 0, $pos);
                }
                $type = strtoupper($type);
                if (in_array($type, $verbForms)) {
                    $found = true;
                    break;
                }

                $tags = array_keys($lexeme['tags']);
                if (count($tags) > 1) {
                    $type = strtoupper($tags[1]);                   // this can happen if there is a noun form of the verb e.g. feed
                    $pos = strpos($type, "-");
                    if ($pos !== false) {
                        $type = substr($type, 0, $pos);
                    }
                    if (in_array($type, $verbForms)) {
                        $found = true;
                        break;
                    }
                }
            }
            if ( $found == false ) {
                $type = null;
            }

            // print($w." ".$nextWord." ".$nextNextWord." VERB:".($found ? $this->words[$vbIndex] : null)." ".$type." \n");

            if ($w === "is" || $w === "am" || $w === "are"||
                (isset($type) && ($type == "VB" || $type == "VBZ")) && $w !== "have" && $w != "has" && $w !== "will" && $w !== "shall") {
                if (isset($type)) {
                    if ($type === "VB" || $type == "VBZ") {
                        $this->present++;
                        $this->simple++;
                        $ii = $vbIndex + 1;
                        continue;
                    }
                } else {
                    $this->present++;
                    $this->simple++;
                }
            } elseif ( $w !== "have" && $w != "has" && $w !== "will" && $w !== "shall" &&
                isset($type) && $type === "VBD") {
                $this->past++;
                $this->simple++;
            } elseif ($w == "will" || $w == "shall") {
                if (isset($type) && $type == "VB") {
                    $this->future++;
                    $this->simple++;
                    $ii = $vbIndex + 1;
                    continue;
                } elseif ($nextWord == "be" && isset($type) && $type == "VBG") {
                    $this->continuous++;
                    $this->future++;
                    $ii = $vbIndex + 1;
                    continue;
                } elseif ($nextWord == "have" && isset($type) && ($type == "VBD" || $type == "VBN") ) {
                    $this->perfect++;
                    $this->future++;
                    $ii = $vbIndex + 1;
                    continue;
                } elseif ($nextWord == "have" && isset($type) && $type == "VBG") {
                    $this->perfectContinuous++;
                    $this->future++;
                    $ii = $vbIndex + 1;
                    continue;
                } elseif ($nextWord == "have" && $nextNextWord == "been" && isset($type) && $type == "VBG") {
                    $this->perfectContinuous++;
                    $this->future++;
                    $ii = $vbIndex + 1;
                    continue;
                }
            } elseif ($w == "was" || $w == "were") {
                $this->past++;
                $this->continuous++;
            } elseif ($w == "had") {
                if ($nextWord == "been") {
                    if (isset($type) && $type == "VBG") {
                        $this->perfectContinuous++;
                        $this->past++;
                        $ii = $vbIndex + 1;
                        continue;
                    } else {
                        // not sure here
                        $this->perfectContinous++;
                        $this->past++;
                        $ii = $vbIndex + 1;
                        continue;
                    }
                } else {
                    if (isset($type) && $type == "VBN") {
                        $this->perfect++;
                        $this->past++;
                        $ii = $vbIndex + 1;
                        continue;
                    } else {
                        // not sure here
                        $this->perfect++;
                        $this->past++;
                        $ii = $vbIndex + 1;
                        continue;
                    }
                }
            } elseif ($w == "has" || $w == "have") {
                if (isset($type)) {
                    // VB is required for words like 'read' where the past participle is the same as the base verb
                    if ($type === "VBN" || $type === "VB") {
                        $this->present++;
                        $this->perfect++;
                        $ii = $vbIndex + 1;
                        continue;
                    } elseif ($type === "VBG") {
                        $this->present++;
                        $this->perfectContinuous++;
                        $ii = $vbIndex + 1;
                        continue;
                    }
                }
            }
            $ii++;
        }            
    }

    private function detectVoice() {
        // This is harder a simple way is to detect a "to be" or "to get" plus the past participle
        // there may also be a "by" and a noun.
        // ACTICE:  Tom collected the money.
        // PASSIVE: The money was collected.
        $stack = [];
        for ($ii=0; $ii < count($this->words); $ii++) {
            // var_dump($this->words);
            $lexeme = $this->analysis->get($ii);
            if ( $lexeme === null ) { // this can happen for a piece of punctuation
                // ignore
            } else {
                $type = strtoupper($lexeme['top']);
                if (substr($type, 0, 1) == "N" || substr($type,0,3) == "PPS") {
                    $stack[] = "NN";
                }
                if (substr($type, 0, 2) == "BE" || substr($type, 0, 2) == "HV" || 
                    $this->words[$ii] == "got" || $this->words[$ii] == "gotten" ) {
                    if (count($stack) > 1 ) {
                        if ( $stack[count($stack)-1] != "BE") {
                            $stack[] = "BE";
                        } 
                    } else {
                        $stack[] = "BE";
                    }
                }
                if (substr($type, 0, 2) == "VB") {
                    $stack[] = "VB";
                }
            }
        }
        // var_dump($stack);
        if (count($stack) >= 3) {
            if ($stack[0] == "NN" &&  $stack[1] == "BE" && $stack[2] == "VB") {
                $this->voice = "PASSIVE";
                return;
            }
            if ($stack[0] == "BE" &&  $stack[1] == "BE" && $stack[2] == "VB") {
                $this->voice = "PASSIVE";
                return;
            }
        }
        // var_dump($this->analysis->lexemes());
        $this->voice = "ACTIVE";
    }
}