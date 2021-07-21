<?php 

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class class_ElizaBotTest extends TestCase
{
    public function test_getResponse(): void
    {
        $args = [
            "command" => "test",
            "text" => "How are you?"
        ];
        $app = new App($args);
        $app->botSelectionName = "Eliza";
        $bot = new ElizaBot();
        $text = $bot->handle($app);
        $this->assertSame($text, "You have reached Eliza, I am not in right now.");
    }
}
