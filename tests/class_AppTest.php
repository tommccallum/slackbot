<?php 

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class class_AppTest extends TestCase
{
    public function test_createFromCommandLine(): void
    {
        $args = [
            "command" => "test",
            "text" => "How are you?"
        ];
        $app = new App($args);
        $this->assertSame($app->text, $args['text']);
    }
}
