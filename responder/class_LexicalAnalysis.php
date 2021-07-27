<?php


class LexicalAnalysis
{
    private $lexemes = array();
    private $antonym_neg_to_pos_map = [];
    private $personNames = [];

    public function lexemes()
    {
        return $this->lexemes;
    }

    public function __construct()
    {
        $this->antonym_neg_to_pos_map = loadAntonyms();
        $this->personNames = loadPersonNames();
    }

    public function intent()
    {
        $verbs = array();
        foreach ($this->lexemes as $w => $item) {
            if (substr($item['top'], 0, 2) == "vb") {
                array_push($verbs, $w);
            }
        }
        return ($verbs);
    }

    public function verbs()
    {
        $verbs = array();
        foreach ($this->lexemes as $w => $item) {
            if (substr($item['top'], 0, 2) == "vb") {
                array_push($verbs, $w);
            }
        }
        return ($verbs);
    }

    public function get($key)
    {
        if (is_numeric($key)) {
            if ($key < count($this->lexemes)) {
                return $this->lexemes[$key];
            } else {
                return null;
            }
        }
        foreach ($this->lexemes as $lexeme) {
            if ($lexeme['text'] == $key) {
                return $lexeme;
            }
        }
        return null;
    }


    public function nouns()
    {
        $nouns = array();
        foreach ($this->lexemes as $w => $item) {
            if ($item['top'] == "nn") {
                array_push($nouns, $w);
            }
        }
        return ($nouns);
    }


    public function isNegated()
    {
        // we could the number of negations if even then its not negated
        // if its odd then it is.
        $count = 0;
        foreach ($this->lexemes as $w => $item) {
            if (strpos($item['top'], "*") !== false) {
                $count++;
            }
        }
        return $count % 2 == 1;
    }

    public function removeNegations()
    {
        $words = [];
        foreach ($this->lexemes as $w => $item) {
            $keep = true;
            $s = new Stemmer();
            $stemmedWord = $s->stem($w);
            print("STEMMED: $w => $stemmedWord\n");
            if (isset($this->antonym_neg_to_pos_map[$stemmedWord])) {
                $w = $this->antonym_neg_to_pos_map[$stemmedWord];
            } elseif (strpos($item['top'], "*") !== false) {
                if ($w === "not") {
                    // remove completely
                    $keep = false;
                } elseif ($w === "won't") {
                    $w = "will";
                } else {
                    $w = preg_replace("/n\'t/", "", $w);
                }
            }
            if ($keep) {
                array_push($words, $w);
            }
        }
        $this->lexemes = $this->inferPartsOfSpeechArray($words);
    }

    public function set($lexemes)
    {
        $this->lexemes = $lexemes;
    }

    public function words()
    {
        return array_keys($this->lexemes);
    }

    public function getTaggedText()
    {
        $str = "";
        foreach ($this->lexemes as $word => $lexeme) {
            if (is_array($lexeme['tags'])) {
                $tag = key($lexeme['tags']);
            } else {
                $tag = $lexeme['top'];
            }
          
            $str .= $word . '/' . $tag . ' ';
        }
        return $str;
    }

    public function inferPartsOfSpeechArray($words)
    {
        global $conn;
        $lexemes = array();

        foreach ($words as $index => $word) {
            $meta = [
                "index" => $index,
                "original" => $word,
                "text" => strtolower($word)
            ];

            if (preg_match("/^\d+$/", $word)) {
                $meta['value'] = intval($word);
                $meta['type'] = "NUMBER";
                $meta['top'] = "_NUMBER";
                $meta['tags'] = [ '_NUMBER' => ['score' => 1, 'percent' => 100]];
                $lexemes[] = $meta;
                continue;
            }

            if (preg_match("/^-?(?:\d+|\d*\.\d+)$/", $word)) {
                $meta['value'] = doubleval($word);
                $meta['type'] = "NUMBER";
                $meta['top'] = "_NUMBER";
                $meta['tags'] = [ '_NUMBER' => ['score' => 1, 'percent' => 100]];
                $lexemes[] = $meta;
                continue;
            }

            if (substr($word, 0, 2) == "::" && substr($word, -2, 2) == "::") {
                $meta['value'] = substr($word, 2, strlen($word)-4);
                $meta['type'] = "EMOJI";
                $meta['top'] = "_EMOJI";
                $meta['tags'] = [ '_EMOJI' => ['score' => 1, 'percent' => 100]];
                $lexemes[] = $meta;
                continue;
            }

            # categorise known names
            if (in_array($meta['text'], $this->personNames)) {
                $meta['tags'] = [ 'NP' => [ 'score' => 1, 'percent' => 100 ]];
                $meta['type'] = "PERSON";
                $meta['top'] = "NP";
                $lexemes[] = $meta;
                continue;
            }

            if (preg_match("/(\d\.\d\.\d\.\d)$/", $word, $matches)) {
                $learningOutcome = $matches[1];
                $meta['value'] = "LO".$learningOutcome;
                $meta['tags'] = ['NP' => 1];
                $meta['top'] = "NP";
                $meta['valid'] = null;
                $meta['type'] = "LEARNING_OUTCOME";
                $lexemes[] = $meta;
                continue;
            }

            if (preg_match("/^U\w{10}$/", $word, $matches)) {
                $meta['value'] = $word;
                $meta['tags'] = ['NP' => 1];
                $meta['top'] = "NP";
                $meta['type'] = "SLACK_USER";
                $lexemes[] = $meta;
                continue;
            }
            
            $meta['type'] = "WORD";
            
            $sql = "SELECT * FROM `Words` WHERE `Word` = ? LIMIT 1";
            $statement = $conn->prepare($sql);
            $statement->bind_param("s", $word);
            $statement->execute();
            $result = $statement->get_result();

            if (@$result->num_rows > 0) {// We know this Uni-gram
                // Collect the tags for the Uni-gram
                while ($row = mysqli_fetch_assoc($result)) {
                
                    // Decode Uni-gram tags from json into associative array
                    $tags = json_decode($row["Tags"], 1);
                
                    // if there are known tags for the Uni-gram
                    if (!empty($tags)) {
                        // Sort the tags and compute %
                        
                        arsort($tags); // arranges them by value from largest to smallest
                        $sum = array_sum($tags);
                        foreach ($tags as $tag=>&$score) {
                            #$score = $score . ' : ' . ($score/$sum * 100) . '%';

                            $score = [
                                'score' => $score,
                                'percent' => $score/$sum * 100
                            ];
                        }
                    } else {
                        $tags = [ 'unk' => [ 'score' => 1, 'percent' => 100 ] ];
                    }
                    $meta['tags'] = $tags;
                }
            } else {
                // If we don't know this tag, we will take a guess and think its a technical term
                // in which case its most likely a name of something - hence a noun of somesort.
                // If its been used in all caps or first letter is capital then we say its a proper
                // noun.
                if (ctype_upper($meta['original'])) {
                    // if in all capitals like HTML
                    $meta['tags'] = [ 'NP' => [ 'score' => 1, 'percent' => 100 ] ];
                } elseif ($meta['index'] !== 1 && ctype_upper($meta['original'][0])) {
                    // is not start of sentence and has capital e.g. Python
                    $meta['tags'] = [ 'NP' => [ 'score' => 0.8, 'percent' => 80 ],
                                        'NN' => [ 'score' => 0.2, 'percent' => 40 ],
                                     ];
                } else {
                    $meta['tags'] = [ 'NN' => [ 'score' => 0.6, 'percent' => 60 ],
                                        'NP' => [ 'score' => 0.4, 'percent' => 40 ],
                                     ];
                }
            }
            $meta['top'] = strtoupper(key($meta['tags']));
            $lexemes[] = $meta;
        }
        $this->lexemes = $lexemes;
        return $lexemes;
    }
}
