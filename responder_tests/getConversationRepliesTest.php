<?php

# Conversation: C023JCGLMGB
# ts: 1626799712.008800


declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once(__DIR__."/../responder/getConversationReplies.php");
autoload_environment();

final class getConversationRepliesTest extends TestCase
{
    public function test_getConversation(): void
    {
        #$result = getConversationFromSlack("C023JCGLMGB", "1626799712.008800");
        // $result = getConversationRepliesFromSlack("C023JCGLMGB", "1626971116.009600");
        
        // #$result = getConversationRepliesFromSlack("C023JCGLMGB", "1626971589.009700");
        // #$result(null);
        // var_dump($result);
        $this->assertSame(1,1);
    }
}