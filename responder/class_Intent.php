<?php

// used by intent to decide which example was the best quality match
function compareMatchedExamplesByQuality($a, $b)
{
    if ($a['quality'] == $b['quality']) {
        return 0;
    }
    if ($a['quality'] > $b['quality']) {
        return -1;
    } else {
        return 1;
    }
}

// called by bots to decide which intent was found first in the message
function compareIntentMatches($a, $b)
{
    return $a['match'][0]['start_index']  - $b['match'][0]['start_index'];
}

class Intent
{
    private $name = "";
    private $examples = [];
    private $selectionMethod = "";
    private $replies = [];
    private $className = null;
    private $threshold = 0.5;

    public function name()
    {
        return $this->name;
    }

    public function getReply($matchedIntent=null)
    {
        if (isset($this->className)) {
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

    public function parseExample($str)
    {
        $items = explode(" ", $str);
        $tokens = [];
        foreach ($items as $item) {
            if (strlen($item) > 1 && $item[0] == "?") {
                // variables of the form ?u:user or ?topic:any:*;required

                $varDef = substr($item, 1);
                $varDefParts = explode(":", $varDef);
                $name = $varDefParts[0];
                $type = null;
                if (isset($varDefParts[1])) {
                    $type = strtoupper($varDefParts[1]);
                }
                $value = null;
                if ($type == "USER") {
                    $type = "SLACK_USER"; // make it match the lexical analyser
                    $value = strtoupper($varDefParts[0]);
                }
                $length = 1;
                if (isset($varDefParts[2])) {
                    if ($varDefParts[2] === "*") {
                        $length = 0;
                    } else {
                        $length = intval($varDefParts[2]);
                    }
                }
                $required = false;
                if (isset($varDefParts[3])) {
                    $options = strtoupper($varDefParts[3]);
                    if ($options === "REQUIRED") {
                        $required = true;
                    }
                }

                $tokens[] = [
                    'original' => $item,
                    'text' => $name,
                    'type' => $type,
                    'value' => $value,
                    'length' => $length,         // 0 = the rest
                    'required' => $required
                ];
            } else {
                $required = false;
                if ($item[0] == "*") {
                    $required = true;
                    $text = substr($item, 1);
                } else {
                    $text = $item;
                }
                $tokens[] = [
                    'original' => $item,
                    'text' => strtolower($text),
                    'type' => 'BASIC',
                    'required' => $required
                ];
            }
        }
        return $tokens;
    }


    // pass the exampleAST from above
    public function matchExampleToClause($exampleAst, $clauseModel)
    {
        $ii=0;
        $jj=0;
        $matchCount = 0; // score on how well we match
        $totalCount = count($exampleAst);
        $match = [];
        $clause = $clauseModel['lexemes'];
        while ($ii < count($exampleAst)) {
            if ($exampleAst[$ii]['type'] == "BASIC") {
                if ($exampleAst[$ii]['required']) {
                    $found = false;
                    while ($jj < count($clause)) {
                        // TODO use the stemmer here to add a little more generality
                        // var_dump($clause[$jj]);
                        // var_dump($exampleAst[$ii]);
                        if ($clause[$jj]['text'] == $exampleAst[$ii]['text']) {
                            // great ok
                            $found = true;
                            break;
                        } else {
                            $jj++;
                        }
                    }
                    if (!$found) {
                        return false;
                    }
                    $matchCount++;
                    $match[] = [
                        'exampleNode' => $exampleAst[$ii],
                        'matchedNodes' => [ $clause[$jj] ],
                        'index' => $jj
                    ];
                    $jj++;
                } else {
                    // we try to match if we can
                    if ($clause[$jj]['text'] == $exampleAst[$ii]['text']) {
                        $matchCount++;
                        $match[] = [
                            'exampleNode' => $exampleAst[$ii],
                            'matchedNodes' => [ $clause[$jj] ],
                            'index' => $jj
                        ];
                        $jj++;
                    }
                }
            } else {
                if ($exampleAst[$ii]['type'] == "SLACK_USER") {
                    if ($exampleAst[$ii]['required']) {
                        $found = false;
                        while ($jj < count($clause)) {
                            if ($clause[$jj]['type'] == "SLACK_USER") {
                                $found = true;
                                break;
                            } else {
                                $jj++;
                            }
                        }
                        if (!$found) {
                            return false;
                        }
                        $matchCount++;
                        $match[] = [
                            'exampleNode' => $exampleAst[$ii],
                            'matchedNodes' => [ $clause[$jj] ],
                            'index' => $jj
                        ];
                    } else {
                        // we try to match if we can
                        if ($clause[$jj]['type'] == $exampleAst[$ii]['type']) {
                            $matchCount++;
                            $match[] = [
                                'exampleNode' => $exampleAst[$ii],
                                'matchedNodes' => [ $clause[$jj] ],
                                'index' => $jj
                            ];
                            $jj++;
                        }
                    }
                } elseif ($exampleAst[$ii]['type'] == "LEARNING_OUTCOME") {
                    if ($exampleAst[$ii]['required']) {
                        $found = false;
                        while ($jj < count($clause)) {
                            if ($clause[$jj]['type'] == "LEARNING_OUTCOME") {
                                $found = true;
                                break;
                            } else {
                                $jj++;
                            }
                        }
                        if (!$found) {
                            return false;
                        }
                        $matchCount++;
                        $match[] = [
                            'exampleNode' => $exampleAst[$ii],
                            'matchedNodes' => [ $clause[$jj] ],
                            'index' => $jj
                        ];
                    } else {
                        // we try to match if we can
                        if ($clause[$jj]['type'] == $exampleAst[$ii]['type']) {
                            $matchCount++;
                            $match[] = [
                                'exampleNode' => $exampleAst[$ii],
                                'matchedNodes' => [ $clause[$jj] ],
                                'index' => $jj
                            ];
                            $jj++;
                        }
                    }
                } elseif ($exampleAst[$ii]['type'] == "ANY") {
                    // can match any type
                    if ($exampleAst[$ii]['length'] == 0) {
                        // match up to the end of the clause
                        $nextNode = null;
                        if ($ii < count($exampleAst)-1) {
                            $nextNode = $exampleAst[$ii+1];
                        }
                        $bag = [];
                        for (; $jj < count($clause); $jj++) {
                            if (isset($nextNode)) {
                                if ($nextNode['type'] == "BASIC") {
                                    if ($nextNode['text'] == $clause[$jj]['text']) {
                                        break;
                                    }
                                } else {
                                    if ($nextNode['type'] == $clause[$jj]['type']) {
                                        break;
                                    }
                                }
                            }
                            $bag[] = $clause[$jj];
                        }
                        if (count($bag) > 0) {
                            $match[] = [
                                'exampleNode' => $exampleAst[$ii],
                                'matchedNodes' => $bag,
                                'index' => $jj
                            ];
                            $matchCount++;
                        } else {
                            // we did not manage to complete this variable
                            return false;
                        }
                    } else {
                        $bag = [];
                        for ($kk=0; $kk < $exampleAst[$ii]['length']; $kk) {
                            $bag[] = $clause[$jj];
                            $jj++;
                        }
                        if (count($bag) > 0) {
                            $match[] = [
                                'exampleNode' => $exampleAst[$ii],
                                'matchedNodes' => $bag,
                                'index' => $jj
                            ];
                            $matchCount++;
                        } else {
                            return false;
                        }
                    }
                }
            }
            $ii++;
        }
        if (count($match) === 0) {        // we should not need this but you never know!
            return false;
        }
        $matchResult = [
            'quality' => $matchCount / $totalCount,
            'matches' => $match,
            'start_index' => $match[0]['index'],
            'match_count' => $matchCount,
            'total_count' => $totalCount
        ];
        return $matchResult;
    }


    public function isLike($clause)
    {
        # generate an array of { name, action, start_position, end_position }
        $matchedExamples = [];
        foreach ($this->examples as $index => $example) {
            // does our example match the clause we have been handed
            $exampleAst = $this->parseExample($example);
            $result = $this->matchExampleToClause($exampleAst, $clause);
            if ($result !== false && $result['quality'] >= $this->threshold) {
                $result['example'] = $example;
                $result['example_index'] = $index;
                $matchedExamples[] = $result;
            }
        }
        if (count($matchedExamples) == 0) {
            return null;
        }
        # sort by the quality of the match, this should allow us to
        # ensure the best formula wins
        usort($matchedExamples, "compareMatchedExamplesByQuality");
        
        foreach ($matchedExamples as $match) {
            savelog("Intent match: ".$match['example']." ".$match['quality']." ".$match['match_count']." ".$match['total_count']);
            // var_dump("Intent match: ".$match['example']." ".$match['quality']." ".$match['match_count']." ".$match['total_count']);
        }

        $intents = ['name' => $this->name(),
                    'action' => array( $this, "getReply"),
                    'match' => $matchedExamples
                    ];
        return $intents;
    }

    public function loadFromFile($path)
    {
        if (file_exists($path)) {
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
            if (isset($json['threshold'])) {
                $this->threshold = $json['threshold'];
            }
        }
    }
}
