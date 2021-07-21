<?php 

declare(strict_types=1);
use PHPUnit\Framework\TestCase;


final class helloworldTest extends TestCase
{
    public function testHelloWorld(): void
    {
        $this->assertSame(helloWorld(), "helloTestWorld");
    }
}
