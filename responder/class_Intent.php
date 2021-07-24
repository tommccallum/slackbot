<?php

class Intent
{
    private $name = "";
    private $examples = [];
    private $selectionMethod = "";
    private $replies = [];
    private $className = null;

    public function name() {
        return $this->name;
    }

    public function getReply($matchedIntent=null) {
        if ( isset($this->className) ) {
            $cls = new $this->className();
            $reply = $cls->match($matchedIntent);
            return $reply;
        } else {
            if ($this->selectionMethod == "random") {
                $n = rand(0, count($this->replies)-1);
                return $this->replies[$n];
            } else {
                # by default returns first reply
                return $this->replies[0];
            }
        }
    }

    public function isLike($str) {
        # compare $str to the examples in some way.
        # generate an array of { name, action, start_position, end_position }
        $asis_str = splitStringIntoLexemes($str);
        $lc_str = splitStringIntoLexemes(strtolower($str));
        foreach( $this->examples as $example ) {
            $tokens = explode(" ", strtolower($example));
            // search for first instance of the first token
            $startIndex = array_search( $tokens[0], $lc_str);
            if ( $startIndex === false ) {
                continue;
            }
            $index = $startIndex + 1;
            $matchAllTokens = true;
            $matchedVariables = [];
            for( $ii=1; $ii < count($tokens); $ii++ ) {
                if ( substr($tokens[$ii], 0, 1) == "?" && substr($tokens[$ii],-1,1) == "?" ) {
                    // optional match
                    $name = preg_replace("/\?/", "", $tokens[$ii]); // remove all question marks
                    $matchedVariables[$name] = [ 'type' => 'optional', 'name' => $name, 'value' => $asis_str[$index] ];
                } else if ( substr($tokens[$ii], 0, 2) == "%%" && substr($tokens[$ii],-2,2) == "%%" ) {
                    // must have match and should match up and to a full stop, question mark or exclaimation mark.
                    $name = preg_replace("/%/", "", $tokens[$ii]); // remove all %
                    // for now we just give the rest of the message.
                    $restOfSentence = "";
                    for($jj=$index; $jj < count($asis_str); $jj++ ) {
                        $restOfSentence .= " ".$asis_str[$jj];
                    }
                    $matchedVariables[$name] = [ 'type' => 'required', 'name' => $name, 'value' => $restOfSentence ];
                    $index = count($tokens)-1;
                    break;
                } else if ( substr($tokens[$ii], 0, 1) == "%" && substr($tokens[$ii],-1,1) == "%" ) {
                    // must have match
                    $name = preg_replace("/%/", "", $tokens[$ii]); // remove all %
                    $matchedVariables[$name] = [ 'type' => 'required', 'name' => $name, 'value' => $asis_str[$index] ];
                } else {
                    if ( $tokens[$ii] != $lc_str[$index] ) {
                        $matchAllTokens = false;
                        break;
                    }
                }
                $index ++;
            }
            if ( $matchAllTokens ) {
                $intents = ['name' => $this->name(), 
                            'action' => array( $this, "getReply"), 
                            'start_position' => $startIndex, 'end_position' => $index,
                            'variables' => $matchedVariables,
                            'matched_example' => $example
                         ];
                return $intents;
            }
        }
        return null;
    }

    public function loadFromFile($path) {
        if ( file_exists($path) ) {
            $contents = file_get_contents($path);
            $json = json_decode($contents, true);
            if (isset($json['name'])) {
                $this->name = $json['name'];
            }
            if (isset($json['examples'])) {
                $this->examples = $json['examples'];
            }
            if (isset($json['selection'])) {
                $this->selectionMethod = $json['selection'];
            }
            if (isset($json['replies'])) {
                $this->replies = $json['replies'];
            }
            if (isset($json['class'])) {
                $this->className = $json['class'];
            }
        }
    }
}