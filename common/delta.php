<?php

function delta($userInput, $dialogue ) {
    if (strpos($dialogue, $userInput) !== false) {
        return 0;
	}
	$userInput2 = preg_replace("/\W/", " ", strtolower($userInput));
	$uWords = preg_split("/\s/", $userInput2);
	$dialogue2 = preg_replace("/\W/", " ", strtolower($dialogue));
	$dWords = preg_split("/\s/", $dialogue2);
	$arr = array_intersect($uWords, $dWords);
	if ( count($arr) > count($uWords) * 0.3 ) {
		return true;
	}
	return false;
}
