<?php


class TranslatorBot extends Bot
{
    public $mode = "Normal Translation";

    function printInfo() {
        print("Translating into ".$this->language." \n");
        print("Text to translate: ".$this->text."\n");
        print("Language: ".$this->languageName."\n");
    }

    function selectLanguageToReplyIn($userGivenString) {
        $words = preg_split("/\s/", $userGivenString);
        $this->language = is_language($words[0]);
        if (!$this->language) {
            $this->language = "it";
            $this->languageName = "Italian";
        } else {
            $this->languageName = $this->language[1];
            $this->language = $this->language[0];
            array_shift($words);
            $this->text = join(" ", $words);
        }
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

    public function ask($message)
    {
        if (preg_match("/wh(at|ich) languages do you speak/", $userInputText)) {
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
            $this->app->doTranslation = false;
        }
    }
    
    public function translateMessage($message) {
        if (strlen(trim($message)) == 0) {
            return $message;
        }
        $host = "https://translate.google.com/translate_a/single?client=webapp&sl=auto&tl=".$app->language."&hl=en";
        $host .= "&dt=at&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=sos&dt=ss&dt=t&dt=gt&otf=1&ssel=0&tsel=4&kc=0&tk=&";
        $host .= "q=".urlencode($message);
    
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
        $translated = "";
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
        $translated = trim($translated) . "\n(" . $app->languageName. "; Mode: ".$app->mode."; I said '" . $app->text . "', and she said '" . $app->textToTranslate. "')";
        return ($translated);
    }
}
