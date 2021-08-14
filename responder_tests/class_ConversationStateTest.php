<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class class_ConversationStateTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        (new MongoDB\Client())->slackbot->conversation_state->drop();
    }

    public function test_create_empty(): void
    {
        $state = new ConversationState();
        $this->assertSame($state->save(), false);
        $this->assertSame($state->id(), null);
    }

    public function test_create_with_thread_id(): void
    {
        $state = new ConversationState(1);
        $this->assertSame($state->save(), true);
        $this->assertSame($state->id(), 1);
    }

    public function test_load_empty_with_thread_id(): void
    {
        $state = new ConversationState(1);
        $this->assertSame($state->getLastMessage(), null);
        $this->assertSame($state->getLastMessageWithReply(), null);
        $this->assertSame($state->id(), 1);
    }

    public function test_add_message_fail(): void
    {
        $mockEvent = [
            "no_ts" => 1
        ];
        $state = new ConversationState(1);
        $this->expectException(\Exception::class);
        $state->addMessage($mockEvent);
    }

    public function test_add_message(): void
    {
        $mockEvent = [
            "ts" => 1,
            "user" => "test"
        ];
        $state = new MockConversationState(1);
        $this->assertSame($state->addMessage($mockEvent), true);
        $this->assertSame($state->length(), 1);
        $this->assertSame($state->addMessage($mockEvent), true);
        $this->assertSame($state->length(), 1);
    }

    public function test_add_message_2(): void
    {
        $mockEvent = [
            "ts" => 2,
            "user" => "test"
        ];
        $state = new MockConversationState(1);
        $this->assertSame($state->length(), 1);
        $msg = $state->getLastMessage();
        $this->assertSame($msg['ts'], 1);
        $this->assertSame($state->addMessage($mockEvent), true);
        $this->assertSame($state->length(), 2);
        $this->assertSame($state->addMessage($mockEvent), true);
        $this->assertSame($state->length(), 2);
        $msg = $state->getLastMessage();
        $this->assertSame($msg['ts'], 2);
    }

    public function test_add_message_skip_ts(): void
    {
        $mockEvent = [
            "ts" => 4,
            "user" => "test"
        ];
        $state = new MockConversationState(1);
        $this->assertSame($state->length(), 2);
        $msg = $state->getLastMessage();
        $this->assertSame($msg['ts'], 2);
        $this->assertSame($state->addMessage($mockEvent), true);
        $this->assertSame($state->length(), 3);
        $this->assertSame($state->addMessage($mockEvent), true);
        $this->assertSame($state->length(), 3);
        $msg = $state->getLastMessage();
        $this->assertSame($msg['ts'], 4);
    }

    public function test_add_message_insert_ts(): void
    {
        $mockEvent = [
            "ts" => 3,
            "user" => "test"
        ];
        $state = new MockConversationState(1);
        $this->assertSame($state->length(), 3);
        $msg = $state->getLastMessage();
        $this->assertSame($msg['ts'], 4);
        $this->assertSame($state->addMessage($mockEvent), true);
        $this->assertSame($state->length(), 4);
        $this->assertSame($state->addMessage($mockEvent), true);
        $this->assertSame($state->length(), 4);
        $msg = $state->getLastMessage();
        $this->assertSame($msg['ts'], 4);
    }


    public function test_add_reply(): void
    {
        $mockReply = [
            "stuff" => []
        ];
        $state = new MockConversationState(1);
        $this->assertSame($state->length(), 4);
        $msg = $state->getLastMessage();
        $this->assertSame($msg['ts'], 4);
        $data = $state->getData();
        $this->assertTrue(!isset($data['history'][count($data['history'])-1]['slackbot_reply']));
        $this->assertSame($state->addReply($mockReply), true);
        $data = $state->getData();
        // var_dump($data);
        $this->assertTrue(isset($data['history'][count($data['history'])-1]['slackbot_reply']));
    }

    public function test_add_reply_to_message(): void
    {
        $mockEvent = [
            "ts" => 1,
            "user" => "test"
        ];
        $mockReply = [
            "stuff" => []
        ];
        $state = new MockConversationState(1);
        $this->assertSame($state->length(), 4);
        $msg = $state->getLastMessage();
        $this->assertSame($msg['ts'], 4);
        $data = $state->getData();
        $this->assertTrue(!isset($data['history'][0]['slackbot_reply']));
        $this->assertSame($state->addReply($mockReply, $mockEvent), true);
        $data = $state->getData();
        // var_dump($data);
        $this->assertTrue(isset($data['history'][0]['slackbot_reply']));
    }

    public function test_conversation_load(): void
    {
        // here we are giving it a message that occurs before the end of the
        // thread.  It should ask Slack for the rest of the thread and
        // load up the conversation.
        $file = __DIR__."/messages/conversation_load_test.json";
        $contents = file_get_contents($file);
        $event = json_decode($contents, true);
        $message = $event['event'];
        // var_dump($message['thread_ts']);
        $botId = getAliceId();
        // var_dump($botId);
        $state = new MockConversationState($message, $botId);
        // var_dump($state->getData());
        $this->assertSame($state->length(), 2);
        $this->assertFalse($state->hasMessagesWithoutReply());
    }

    public function test_conversation_load_test_2(): void
    {
        // here the message is initiated by the bot
        $file = __DIR__."/messages/conversation_load_test_2.json";
        $contents = file_get_contents($file);
        $event = json_decode($contents, true);
        $message = $event['event'];
        // var_dump($message['thread_ts']);
        $botId = getAliceId();
        // var_dump($botId);
        $state = new MockConversationState($message, $botId);
        // var_dump($state->getData());
        $this->assertSame($state->length(), 6);
        $this->assertTrue($state->hasMessagesWithoutReply());
    }
}
