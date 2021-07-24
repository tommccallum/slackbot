<?php

# Conversation: C023JCGLMGB
# ts: 1626799712.008800


declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once(__DIR__."/../responder/getConversationHistory.php");
autoload_environment();

final class getConversationHistoryTest extends TestCase
{
    public function test_getConversation(): void
    {
        // $result = getConversationHistoryFromSlack("C023JCGLMGB");
        // var_dump($result);

        // #$ts = $result['messages'][count($result['messages'])-1]['ts'];
        // $ts = $result['response_metadata']['next_cursor'];
        // var_dump($ts);

        // $result = getConversationHistoryFromSlack("C023JCGLMGB", $ts);
        // var_dump($result);
        $this->assertSame(1,1);
    }
}