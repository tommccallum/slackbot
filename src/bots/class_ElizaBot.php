<?php


class ElizaBot extends Bot
{
    public $mode = "Eliza Therapist";

    protected function respond($question)
    {
        $eliza_path = "./eliza/eliza/eliza.py";
        if (!file_exists($eliza_path)) {
            return "You have reached Eliza, I am not in right now.";
        }
        $response = trim(shell_exec("echo '".$question."' | python3 ./eliza/eliza/eliza.py"));
        return $response;
    }
}