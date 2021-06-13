<?php 

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class SlackIOTest extends TestCase
{
    public function testSendSlackMessage(): void
    {
        $args = [
            "command" => "post",
            "text" => "Hello world"
        ];
        $app = new App($args);
        $message = "Hello World!";
        
        sendSlackMessage($app, $message);
    }
}
