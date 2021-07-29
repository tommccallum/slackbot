<?php

abstract class Bot
{
    protected $intents = [];
    protected $partOfDay = [];
    protected $app;

    public function __construct(&$app)
    {
        $this->app = &$app;
    }

    public function getPartOfDay()
    {
        return $this->partOfDay;
    }

    public function setPartOfDay($partOfDay)
    {
        $this->partOfDay = $partOfDay;
    }

    public function addIntent($intent)
    {
        array_push($this->intents, $intent);
    }

    public function removeIntent($name)
    {
        foreach ($this->intents as $key => $value) {
            if ($value->name() == $name) {
                unset($this->intents[$key]);
                return;
            }
        }
    }

    public function printInfo()
    {
        /** Do nothing its a virtual function to be overriden by each bot */
    }

    protected function respond($question)
    {
        /** Do nothing and override */
        return null;
    }

    protected function obey()
    {
        $response = $this->respond($this->app->text);
        if ($response == null || strlen($response) == 0) {
            $response = "Sorry, I am new around here, I do not understand your question.";
        }
        return $response;
    }

    protected function onAppMention()
    {
        return "I understand an AppMention event, but there is nothing I can do.";
    }

    protected function onMessage()
    {
        return "I understand an Message event, but there is nothing I can do.";
    }

    protected function onSomeoneHasJoinedTheChannel()
    {
        return "Welcome friend!";
    }

    protected function newBotAddedToChannel()
    {
        return null;
    }

    public function shouldBotReplyToEvent()
    {
        return false;
    }
    
    protected function didThisBotStartTheConversation()
    {
        return false;
    }

    protected function didThisBotGetMentionedInTheConversation()
    {
        return false;
    }

    // Main dispatch method to handle the different events that we will receive.
    public function handle()
    {
        if ($this->app->isCommand()) {
            savelog("Bot::handle(command)");
            return $this->obey();
        }
        if ($this->app->isEvent()) {
            $eventType = $this->app->event['type'];
            $eventSubtype = null;
            if (isset($this->app->event['subtype'])) {
                $eventSubtype = $this->app->event['subtype'];
            }
            savelog("Bot::handle(event=".$eventType .")");
            if ($eventType == "app_mention") {
                return $this->onAppMention();
            } elseif ($eventType == "member_left_channel") {
            } elseif ($eventType == "member_join_channel") {
            } elseif ($eventType == "message") {
                if (isset($eventSubtype)) {
                    if ($eventSubtype == "channel_join") {
                        return $this->onSomeoneHasJoinedTheChannel();
                    } elseif ($eventSubtype == "bot_add") {
                        return $this->newBotAddedToChannel();
                    } else {
                        return $this->onMessage();
                    }
                } else {
                    return $this->onMessage();
                }
            } else {
                return "Sorry, I do not understand how to respond to a '"+$eventType+"' message.";
            }
        }
        savelog("Bot::handle(null)");
        return null;
    }
}
