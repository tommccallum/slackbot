<?php 

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class class_TestBotTest extends TestCase
{
    public function test_getResponse(): void
    {
        $args = [
            "command" => "test",
            "text" => "How are you?"
        ];
        $app = new App($args);
        $app->botSelectionName = "Test";
        $bot = new TestBot();
        $text = $bot->handle($app);
        $expectedString = "Hi, this is a test response";
        $actual = substr($text, 0, strlen($expectedString));
        $this->assertSame($actual, $expectedString);
    }
}
