<?php

# Requires these 3 variables in .htenv.php
# $slack_webhook_url = // web url from Slack website
# $oauth_token= // oAuth token from slack website
# $icon_url= // local icon

require_once(".htenv.php");

$isSlack = 1;					// flag for if we are being called from slack or from command line
$eliza = 0;						// use Eliza to write a response or do a straight translation
$simpsons = 1;
$doTranslation  = true;			// do a translation or return current contents of $translated variable

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

function readSimpsons($userInput) {
    $data = file_get_contents("eliza/simpsons_dataset.csv");
	$N = strlen($data);
	$response = "";
	$field=0;
	$dialogue = "";
	$responseIsNext = false;
	$matchedDialogue = "";
	for ($ii=0; $ii < $N; $ii++) {
		$ch = substr($data,$ii,1);
		if ( $ch == "," ) {
			$field++;
			if ( $field == 1 ) {
				$response = "";
				$dialogue = "";
			}
		}
		if ( $ch == "\n")  {
			// print("dialogue: ".$dialogue."\n");
			// print("response: ".$response."\n");
			if ( $responseIsNext && trim($response) != "" ) {
				return array( $matchedDialogue, trim($response));
			}
			if ( delta($userInput, $dialogue ) && rand() / getrandmax() < 0.5 ) {
				$responseIsNext = true;
				$matchedDialogue = $dialogue;
			}
			$dialogue = "";
			$field = 0;
		}
		if ( $field > 0 && $ch != "\"" && !($field == 1 && $ch == ",") ) {
			if ( $responseIsNext ) {
				$response .= $ch;
			} else {
                $dialogue .= $ch;
            }
		}
	}
	return array($dialogue, $response);
}

function is_language($userLanguage)
{
    if ($userLanguage === "") {
        return false;
    }
    $languageInput = file_get_contents("languages.txt");
    $lines = preg_split("/\n/", $languageInput);
    foreach ($lines as $line) {
        $words = preg_split("/\s/", $line);
        $n = count($words);
        $code = $words[$n-1];
        array_pop($words);
        $languageName = join("-", $words);

        //print($code . " ". $languageName . " " . $userLanguage. " \n");

        if (strtolower($userLanguage) === "chinese") {
            if ($code = "zh-CN") {
                return array( $code, $languageName );
            }
        }
        if (strtolower($userLanguage) == strtolower($code) ||
            strtolower($userLanguage) === strtolower($languageName)) {
            return array( $code, $languageName );
        }
    }
    return false;
}

$whichMode = rand() / getrandmax();
if ( $whichMode < 0.3 ) {
	$eliza = 0;
	$simpsons = 0;
	$mode = "Normal Translation";
} else if ( $whichMode < 0.6 ) {
	$eliza = 1;
	$simpsons = 0;
	$mode = "Eliza Therapist";
} else {
	$simpsons = 1;
	$mode = "Simpsons";
}

if (isset($_POST['command'])) {
    $command = $_POST['command'];
    $text = $_POST['text'];
    $token = $_POST['token'];
    $channelId = $_POST['channel_id'];
    $user_name = $_POST['user_name'];
} else {
    $text = $argv;
    array_shift($text); // remove first argument
	$text = join(" ", $text);
	$isSlack = 0;
}

$words = preg_split("/\s/", $text);
$language = is_language($words[0]);
if (!$language) {
	$language = "it";
	$languageName = "Italian";
} else {
	$languageName = $language[1];
	$language = $language[0];
	array_shift($words);
	$text = join(" ", $words);
}
if (preg_match("/wh(at|ich) languages do you speak/", $text)) {
	$translated = "I speak the following languages: ";
	$contents = file_get_contents("languages.txt");
	$lines = preg_split("/\n/", $contents);
	$myLanguages = "";
	foreach ($lines as $line) {
		$words = preg_split("/\s/", $line);
		$n = count($words);
		$code = $words[$n-1];
		array_pop($words);
		$languageName = join("-", $words);
		$myLanguages .= $languageName." (".$code."), ";
	}
	$myLanguages = preg_replace("/, $/", "", $myLanguages);
	$translated .= $myLanguages;
	$doTranslation = false;
} else {
	if ($eliza) {
		$textToTranslate = trim(shell_exec("echo '".$text."' | python3 ./eliza/eliza/eliza.py"));
	}
	if ( $simpsons ) {
		$resp = readSimpsons( $text );
        if (strlen(trim($resp[1])) > 0) {
            $textToTranslate = $resp[1];
            $text .= ";Dialogue:".$resp[0];
        }
	}
	if ( $textToTranslate == "" ) {
		$textToTranslate = $text;
	}
}

if ( !$isSlack ) {
    print("Translating into ".$language." \n");
    print("Text to translate: ".$text."\n");
    print("Language: ".$languageName."\n");
}

if ($doTranslation) {
    $host = "https://translate.google.com/translate_a/single?client=webapp&sl=auto&tl=".$language."&hl=en";
    $host .= "&dt=at&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=sos&dt=ss&dt=t&dt=gt&otf=1&ssel=0&tsel=4&kc=0&tk=&";
    if ($textToTranslate) {
        $host .= "q=".urlencode($textToTranslate);
    } else {
        $host .= "q=".urlencode($text);
    }

    $curl = curl_init();

    curl_setopt_array($curl, array(
		CURLOPT_URL => $host,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
			"Accept: */*",
			"Accept-Encoding: gzip, deflate",
			"Cache-Control: no-cache",
			"Connection: keep-alive",
			"Host: translate.google.com",
			"cache-control: no-cache"
		),
	));
    $response  = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    if ($response === null) {
        $translated = "are you not speaking to me google?";
    } else {
        $respJson = json_decode($response);
        if ($respJson[0] == null) {
            $translated = "invalid language probably, try again.";
        } else {
            $translated = "";
            foreach ($respJson[0] as $a) {
                if ($translated == "") {
                    $translated = $a[0];
                } else {
                    $translated .= $a[0];
                }
            }
        }
        if ($translated === null) {
            $translated = "ahh!! (".$httpcode.")";
        }
    }
    curl_close($curl);
    $translated = trim($translated) . "\n(" . $languageName. "; Mode: ".$mode."; I said '" . $text . "', and she said '" . $textToTranslate. "')";
}

if ($isSlack) {
    $data = array(
		"username" => "rainbow",
		"channel" => $channelId,
		"text" => $translated,
		"mrkdwn" => true,
		"icon_url" => $icon_url,
		"attachments" => null
	);

    $json = json_encode($data);
    $slack_call = curl_init($slack_webhook_url);
    curl_setopt($slack_call, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($slack_call, CURLOPT_POSTFIELDS, $json);
    curl_setopt($slack_call, CURLOPT_CRLF, true);
    curl_setopt($slack_call, CURLOPT_RETURNTRANSFER, true);
    curl_setopt(
        $slack_call,
        CURLOPT_HTTPHEADER,
        array(
    "Content-Type: application/json",
    "Content-Length: " . strlen($json))
    );
    $result = curl_exec($slack_call);
    curl_close($slack_call);
} else {
    if (isset($response)) {
        var_dump($response);
    }
    var_dump($translated);
}
