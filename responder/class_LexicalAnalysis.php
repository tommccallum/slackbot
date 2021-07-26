<?php


class LexicalAnalysis
{
    private $lexemes = array();
    private $antonym_neg_to_pos_map = [];

    public function lexemes() {
        return $this->lexemes;
    }

    public function __construct() {
        $this->antonym_neg_to_pos_map = loadAntonyms();
    }

    public function intent() {
        $verbs = array();
        foreach( $this->lexemes as $w => $item ) {
            if ( substr($item['top'],0,2) == "vb" ) {
                array_push($verbs, $w);
            }
        }
        return ( $verbs );
    }

    public function verbs() {
        $verbs = array();
        foreach( $this->lexemes as $w => $item ) {
            if ( substr($item['top'],0,2) == "vb" ) {
                array_push($verbs, $w);
            }
        }
        return ( $verbs );
    }

    public function get($key) {
        if ( is_numeric($key) ) {
            $keys = array_keys($this->lexemes);
            if ($key < count($keys)) {
                $key = $keys[$key];
            } else {
                return null;
            }
        }
        return $this->lexemes[$key];
    }


    public function nouns() {
        $nouns = array();
        foreach( $this->lexemes as $w => $item ) {
            if ( $item['top'] == "nn" ) {
                array_push($nouns, $w);
            }
        }
        return ( $nouns );
    }


    public function isNegated() {
        // we could the number of negations if even then its not negated
        // if its odd then it is.
        $count = 0;
        foreach($this->lexemes as $w => $item ) {
            if (strpos($item['top'], "*") !== false) {
                $count++;
            }
        }
        return $count % 2 == 1;
    }

    public function removeNegations() {
        $words = [];
        foreach($this->lexemes as $w => $item ) {
            $keep = true;
            $s = new Stemmer();
            $stemmedWord = $s->stem($w);
            print("STEMMED: $w => $stemmedWord\n");
            if ( isset($this->antonym_neg_to_pos_map[$stemmedWord]) ) {
                $w = $this->antonym_neg_to_pos_map[$stemmedWord];
            } else if (strpos($item['top'], "*") !== false) {
                if ( $w === "not" ) {
                    // remove completely
                    $keep = false;
                } else if ( $w === "won't" ) {
                    $w = "will";
                    
                } else {
                    $w = preg_replace("/n\'t/", "", $w);
                }
            }
            if ( $keep ) {
                array_push($words, $w);
            }
        }
        $this->lexemes = $this->inferPartsOfSpeechArray($words);
    }

    public function set($lexemes) {
        $this->lexemes = $lexemes;
    }

    public function words() {
        return array_keys($this->lexemes);
    }

    public function getTaggedText() {
        $str = "";
        foreach($this->lexemes as $word => $lexeme){
            if(is_array($lexeme['tags'])){
              $tag = key($lexeme['tags']);
            }
            else{
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

        foreach ($words as $word) {
            // TODO this may be quicker to search for all words at the same time rather than one by one and taking the first entry.
            $sql = "SELECT * FROM `Words` WHERE `Word` = ? LIMIT 1";
            $statement = $conn->prepare($sql);
            $statement->bind_param("s", $word);
            $statement->execute();
            $result = $statement->get_result();

            if (@$result->num_rows > 0) {// We know this Uni-gram
                // Collect the tags for the Uni-gram
                while ($row = mysqli_fetch_assoc($result)) {
                
                    // Decode Uni-gram tags from json into associtive array
                    $tags = json_decode($row["Tags"], 1);
                
                    // if there are known tags for the Uni-gram
                    if (!empty($tags)) {
                        // Sort the tags and compute %
                        arsort($tags);
                        $sum = array_sum($tags);
                        foreach ($tags as $tag=>&$score) {
                            $score = $score . ' : ' . ($score/$sum * 100) . '%';
                        }
                    } else {
                        $tags = array('unk'=>'1 : 100%');
                    }
                
                    $lexemes[$word] = array('lexeme'=>$word, 'tags'=> $tags, 'top' => key($tags) );
                }
            } else { // We don't know this Tag
                $lexemes[$word] = array('lexeme'=>$word, 'tags'=> array('unk'=>'1 : 100%'));
                $lexemes[$word]['top'] = key($lexemes[$word]['tags']);
            }
        }

        $this->lexemes = $lexemes;
        return $lexemes;
    }


}



