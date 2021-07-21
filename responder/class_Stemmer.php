<?php

# Using the porter stemming algorithm
# http://snowball.tartarus.org/algorithms/porter/stemmer.html
# http://snowball.tartarus.org/algorithms/english/stemmer.html
# UTF-8 characters are ignored

class Stemmer
{
    private $language = 'en';
    private $vowels = [ 'a', 'e', 'i', 'o', 'u' ];
    private $exceptions = [];

    public function constructor() {
        $this->loadExceptions();
    }

    public function isConsonent($str, $index) {
        $str = strtolower($str);
        if ( in_array($str[$index], $this->vowels)  === true ) {
            return false;
        }
        if ( $str[$index] === 'y' ) {
            if ( $index > 0 && in_array($str[$index-1], $this->vowels) === false ) {
                return false;
            }
        }
        return true;
    }

    public function vowelCount($str) {
        $v = 0;
        for ($ii=0; $ii < strlen($str); $ii++) {
            if( $this->isConsonent($str, $ii) === false ) {
                $v++;
            }
        }
        return $v;
    }

    public function endsInDoubleConsonent($str) {
        if ( strlen($str) < 2 ) {
            return false;
        }
        if ( $this->isConsonent($str, strlen($str)-1 ) 
            && $this->isConsonent($str, strlen($str)-2) ) {
                return true;
            }
        return false;
    }

    public function stemEndsCVC($str) {
        $a = substr($str, -1);
        $b = substr($str, -2, 1);
        $c = substr($str, -3, 1);
        if ( $this->isConsonent($str, strlen($str)-1) 
            && $this->isConsonent($str, strlen($str)-2) === false
            && $this->isConsonent($str, strlen($str)-3) ) {
            $x = substr($str, -1);
            if ( $x != "x" && $x != "w" && $x != "y" ) {
                return true;
            }
        }
        return false;
    }

    public function measure($str) {
        $measure = 0;
        $last = "";
        for ($ii=0; $ii < strlen($str); $ii++) {
            if( $this->isConsonent($str, $ii) ) {
                if ($last === "V") {
                    $measure++;
                }
                $last = "C";
            } else {
                $last = "V";
            }
        }
        return $measure;
    }

    public function step1a($str) {
        $suffix = substr($str,-4);
        $stem = substr($str,0,strlen($str)-4);
        if ( $suffix === "sses" ) {
            return $stem . "ss";
        }
        $suffix = substr($str,-3);
        $stem = substr($str,0,strlen($str)-3);
        if ( $suffix === "ies" ) {
            return $stem . "i";
        } 
        $suffix = substr($str,-2);
        $stem = substr($str,0,strlen($str)-2);
        if ( $suffix === "ss" ) {
            return $stem . "ss";
        }
        $suffix = substr($str,-1);
        $stem = substr($str,0,strlen($str)-1);
        if ( $suffix === "s" ) {
            return $stem;
        }
        return ( $str );
    }

    public function step1b_2($stem, $suffix) {
        $s = substr($stem,-2);
        if ( $s == "at" ) {
            return $stem . "e";
        } 
        if ( $s == "bl") {
            return $stem . "e";
        }
        if ( $s == "iz" ) {
            return $stem . "e";
        }
        if ( $this->endsInDoubleConsonent($stem) ) {
            $endsWith = substr($stem,-1);
            if ( $endsWith != "l" && $endsWith != "s" && $endsWith != "z" ) {
                $stem = substr($stem, 0, strlen($stem)-1);
                return ( $stem );
            } else {
                return ($stem);
            }
        }
        $m = $this->measure($stem);
        if ( $m == 1 ) {
            if ( $this->stemEndsCVC($stem) ) {
                return $stem . "e";
            }
        }
        return $stem;
    }

    public function step1b($str) {
        $suffix = substr($str,-3);
        $stem = substr($str,0,strlen($str)-3);
        $m = $this->measure($stem);
        #print("$stem $suffix $m\n");
        if ( $suffix === "eed" ) {
            if ( $m > 0 ) {
                return $stem . "ee";
            } else {
                return $str;
            }
        }
        $stem = substr($str,0,strlen($str)-2);
        if ( $this->vowelCount($stem) > 0 ) {
            $suffix = substr($str,-2);
            if ( $suffix === "ed" ) {
                return $this->step1b_2($stem, $suffix);
            }    
        }
        $stem = substr($str,0,strlen($str)-3);
        if ( $this->vowelCount($stem) > 0 ) {
            $suffix = substr($str,-3);
            if ( $suffix === "ing" ) {
                return $this->step1b_2($stem, $suffix);
            }    
        }
        return ( $str );
    }

    public function step1c($str) {
        $stem = substr($str, 0, strlen($str)-1);
        if ( $this->vowelCount($stem) > 0 )  {
            return $stem . "i";
        }
        return $str;
    }

    public function step2($str) {
        $m = $this->measure($str);
        if ( $m == 0 ) return $str;
        $penultimate_letter = substr($str, -2, 1);
        if ( $penultimate_letter == "a" ) {
            if ( preg_match("/ational$/", $str) ) {
                return preg_replace("/ational$/", "ate", $str);
            }
            if ( preg_match("/tional$/", $str) ) {
                return preg_replace("/tional$/", "tion", $str);
            }
        } 
        if ( $penultimate_letter == "c" ) {
            if ( preg_match("/enci$/", $str) ) {
                return preg_replace("/enci$/", "ence", $str);
            }
            if ( preg_match("/anci$/", $str) ) {
                return preg_replace("/anci$/", "ance", $str);
            }
        }
        if ( $penultimate_letter == "e" ) {
            if ( preg_match("/izer$/", $str) ) {
                return preg_replace("/izer$/", "ize", $str);
            }
        }
        if ( $penultimate_letter == "l" ) {
            if ( preg_match("/abli$/", $str) ) {
                return preg_replace("/abli$/", "able", $str);
            }
            if ( preg_match("/alli$/", $str) ) {
                return preg_replace("/alli$/", "al", $str);
            }
            if ( preg_match("/entli$/", $str) ) {
                return preg_replace("/entli$/", "ent", $str);
            }
            if ( preg_match("/eli$/", $str) ) {
                return preg_replace("/eli$/", "e", $str);
            }
            if ( preg_match("/ousli$/", $str) ) {
                return preg_replace("/ousli$/", "ous", $str);
            }
        }
        if ($penultimate_letter == "o") {
            if (preg_match("/ization$/", $str)) {
                return preg_replace("/ization$/", "ize", $str);
            }
            if (preg_match("/ation$/", $str)) {
                return preg_replace("/ation$/", "ate", $str);
            }
            if (preg_match("/ator$/", $str)) {
                return preg_replace("/ator$/", "ate", $str);
            }
        }
        if ($penultimate_letter == "s") {
            if (preg_match("/alism$/", $str)) {
                return preg_replace("/alism$/", "al", $str);
            }
            if (preg_match("/iveness$/", $str)) {
                return preg_replace("/iveness$/", "ive", $str);
            }
            if (preg_match("/fulness$/", $str)) {
                return preg_replace("/fulness$/", "ful", $str);
            }
            if (preg_match("/ousness$/", $str)) {
                return preg_replace("/ousness$/", "ous", $str);
            }
        }
        if ($penultimate_letter == "t") {
            if (preg_match("/aliti$/", $str)) {
                return preg_replace("/aliti$/", "al", $str);
            }
            if (preg_match("/iviti$/", $str)) {
                return preg_replace("/iviti$/", "ive", $str);
            }
            if (preg_match("/biliti$/", $str)) {
                return preg_replace("/biliti$/", "ble", $str);
            }
        }
        return $str;
    
    }

    public function step3($str) {
        $m = $this->measure($str);
        if ( $m == 0 ) return $str;
        if (preg_match("/icate$/", $str)) {
            return preg_replace("/icate$/", "ic", $str);
        }
        if (preg_match("/ative$/", $str)) {
            return preg_replace("/ative$/", "", $str);
        }
        if (preg_match("/alize$/", $str)) {
            return preg_replace("/alize$/", "al", $str);
        }
        if (preg_match("/alise$/", $str)) {
            return preg_replace("/alise$/", "al", $str);
        }
        if (preg_match("/iciti$/", $str)) {
            return preg_replace("/iciti$/", "ic", $str);
        }
        if (preg_match("/ical$/", $str)) {
            return preg_replace("/ical$/", "ic", $str);
        }
        if (preg_match("/ful$/", $str)) {
            return preg_replace("/ful$/", "", $str);
        }
        if (preg_match("/ness$/", $str)) {
            return preg_replace("/ness$/", "", $str);
        }
        return $str;
    }


    public function step4($str) {
        $m = $this->measure($str);
        if ( $m <= 1 ) return $str;
        $step4_suffixes = [ "al", "ance", "ence", "er", "ic", 
                            "able", "ible", "ant", "ement", "ment", 
                            "ent", "ou", "ism", "ate", "iti", 
                            "ous", "ive", "ize"];
        foreach( $step4_suffixes as $sfx ) {
            if (preg_match("/".$sfx."$/", $str)) {
                return preg_replace("/".$sfx."$/", "", $str);
            }
        }
        if (preg_match("/ion$/", $str)) {
            $stem = substr($str, 0, strlen($str)-3);
            if ( substr($stem,-1) == "s" || substr($stem,-1) == "t" ) {
                return preg_replace("/ion$/", "", $str);
            }
        }
        return $str;
    }

    public function step5a($str) {
        $m = $this->measure($str);
        if ( $m > 1 ) {
            if ( substr($str,-1) == "e" ) {
                return substr($str, 0, strlen($str)-1);
            }
        }
        $stem = substr($str, 0, strlen($str)-1);
        if ( $m == 1 && $this->stemEndsCVC($stem) == false ) {
            return substr($str, 0, strlen($str)-1);
        }
        return $str;
    }

    public function step5b($str) {
        // ll -> l
        $m = $this->measure($str);
        if ( $m > 1 
            && $this->endsInDoubleConsonent($str) 
            && substr($str,-1) == "l") {
            $x = substr($str, 0, strlen($str)-2);
            return $x . "l";
        }
        return $str;
    }

    public function stem($str) {
        $str = strtolower($str);
        if ( in_array($str, $this->exceptions) ) {
            return $str;
        }
        $strX = $this->step1a($str);
        if ( $str != $strX ) return $strX;
        $strX = $this->step1b($str);
        if ( $str != $strX ) return $strX;

        $m = $this->measure($str);
        if ( $m > 0 ) {
            $strX = $this->step2($str);
            if ( $str != $strX ) return $strX;
            $strX = $this->step3($str);
            if ( $str != $strX ) return $strX;
        }
        if ( $m > 1 ) {
            $strX = $this->step4($str);
            if ( $str != $strX ) return $strX;
        }
        $strX = $this->step5a($str);
        if ( $str != $strX ) return $strX;
        $strX = $this->step5b($str);
        if ( $str != $strX ) return $strX;
        return ( $str);
    }

    public function addException($w) {
        array_push($this->exceptions, $w);
    }

    private function loadExceptions() {
        $exceptions = array(
            'skis'   => 'ski',
            'skies'  => 'sky',
            'dying'  => 'die',
            'lying'  => 'lie',
            'tying'  => 'tie',
            'idly'   => 'idl',
            'gently' => 'gentl',
            'ugly'   => 'ugli',
            'early'  => 'earli',
            'only'   => 'onli',
            'singly' => 'singl',
            // invariants
            'sky'    => 'sky',
            'news'   => 'news',
            'howe'   => 'howe',
            'atlas'  => 'atlas',
            'cosmos' => 'cosmos',
            'bias'   => 'bias',
            'andes'  => 'andes',

            'inning' => 'inning',
            'outing' => 'outing',
            'canning' => 'canning',
            'herring' => 'herring',
            'earring' => 'earring',
            'proceed' => 'proceed',
            'exceed'  => 'exceed',
            'succeed' => 'succeed',

            'that' => 'that'
        );
        $this->exceptions = array_merge($this->exceptions, $exceptions);
    }

}
