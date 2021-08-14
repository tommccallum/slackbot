<?php

/**
 * Used to access over all dialogues
 */
class DialogueCollection
{
    private $dialogues = [];
    private $dialogPath = __DIR__."/data/dialogues";

    public function loadFromDirectory($dialogPath=null)
    {
        if (isset($dialogPath)) {
            $this->dialogPath = $dialogPath;
        }

        $dialogFiles = getDirContents($this->dialogPath);
        foreach ($dialogFiles as $dialogFile) {
            $dialogue = new Dialogue();
            $dialogue->loadFromFile($dialogFile);
            $this->dialogues[] = $dialogue;
        }
    }

    public function length()
    {
        return count($this->dialogues);
    }
    
    public function matchConversation($conversationState)
    {
        $firstMessage = $this->conversationState->getMessage(0);
        return $this->matchSlackMessage($firstMessage);
    }

    public function matchSlackMessage($message)
    {
        foreach ($this->dialogues as $dialogue) {
            if ($dialogue->match($message)) {
                return true;
            }
        }
        return false;
    }

    public function getMatchingDateTime($dateAsString, $timeAsString)
    {
        $matches = [];
        foreach ($this->dialogues as $dialog) {
            if ($dialog->matchDate($dateAsString) && $dialog->matchTime($timeAsString)) {
                $matches[] = $dialog;
            }
        }
        return $matches;
    }
}
