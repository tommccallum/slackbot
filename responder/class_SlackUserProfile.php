<?php

class SlackUserProfile
{
    private $attributes = [];

    public function __construct($userProfileArray) {
        $this->attributes = $userProfileArray;
    }

    
    public function get($key) {
        $key = strtolower($key);
        if (isset($this->attributes[$key])) {
            return $this->attributes[$key];
        }
        if (isset($this->attributes['profile'][$key])) {
            return $this->attributes['profile'][$key];
        }
        return null;
    }

    public function getKeys() {
        $profileKeys = array_keys($this->attributes['profile']);
        return array_merge($profileKeys, array_keys($this->attributes));
    }

    public function match($matchedIntent) {
        # here we are just going to look for the key in the question
        # its a a bit simple and we can improve it later on to look
        # for synonyms.
        $str = $matchedIntent['matched_example'];
        foreach( $this->attributes as $key => $value ) {
            if ( strpos(strtolower($str), strtolower($key)) !== false ) {
                if( $key == "first_name" ) {
                    $value = ucfirst(strtolower($key));
                } 
                return $value;
            }
        }
        return "Sorry, thats too personal!";
    }
}

function createSlackUserProfile($userId) 
{
    $collection = (new MongoDB\Client)->slackbot->users;
    $userProfileArray = $collection->findOne(["id" => $userId]);
    if ( $userProfileArray === null ) {
        return null;
    }
    return new SlackUserProfile($userProfileArray);
}
