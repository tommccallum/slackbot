<?php

class SimpsonsBot extends Bot
{
    public $mode = "Simpsons";

    protected function respond($question)
    {
        $automated_response = readSimpsons($question);
        if ($automated_response == null) {
            $response = "Simpsons mode is unavailable.";
        } else {
            if (strlen(trim($automated_response[1])) > 0) {
                $response = $automated_response[1];
                $response .= ";Dialogue:".$automated_response[0];
            }
        }
        return $response;
    }


    public function readSimpsons($userInput)
    {
        if (!file_exists("eliza/simpsons_dataset.csv")) {
            return null;
        }
        $data = file_get_contents("eliza/simpsons_dataset.csv");
        $N = strlen($data);
        $response = "";
        $field=0;
        $dialogue = "";
        $responseIsNext = false;
        $matchedDialogue = "";
        for ($ii=0; $ii < $N; $ii++) {
            $ch = substr($data, $ii, 1);
            if ($ch == ",") {
                $field++;
                if ($field == 1) {
                    $response = "";
                    $dialogue = "";
                }
            }
            if ($ch == "\n") {
                // print("dialogue: ".$dialogue."\n");
                // print("response: ".$response."\n");
                if ($responseIsNext && trim($response) != "") {
                    return array( $matchedDialogue, trim($response));
                }
                if (delta($userInput, $dialogue) && rand() / getrandmax() < 0.5) {
                    $responseIsNext = true;
                    $matchedDialogue = $dialogue;
                }
                $dialogue = "";
                $field = 0;
            }
            if ($field > 0 && $ch != "\"" && !($field == 1 && $ch == ",")) {
                if ($responseIsNext) {
                    $response .= $ch;
                } else {
                    $dialogue .= $ch;
                }
            }
        }
        return array($dialogue, $response);
    }
}
