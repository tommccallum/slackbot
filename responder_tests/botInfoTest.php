<?php

# Conversation: C023JCGLMGB
# ts: 1626799712.008800


declare(strict_types=1);
use PHPUnit\Framework\TestCase;

require_once(__DIR__."/../responder/getConversationHistory.php");
autoload_environment();

final class botInfoTest extends TestCase
{
    public function test_getAliceBotInfo(): void
    {
        $collection = (new MongoDB\Client())->slackbot->bots->drop();

        $result = getBotInfo("B025930SRHP");
        // var_dump($result);
        $this->assertSame($result['id'], "B025930SRHP");

        $collection = (new MongoDB\Client())->slackbot->bots;
        $result = $collection->findOne(["id" => "B025930SRHP"]);
        // var_dump($result);
        $this->assertTrue(isset($result));
    }
}
