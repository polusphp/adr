<?php

namespace Polus\Tests\Adr;

use Polus\Adr\DefaultExceptionHandler;
use PHPUnit\Framework\TestCase;
use Polus\Adr\ExceptionDomainPayload;

class DefaultExceptionHandlerTest extends TestCase
{
    public function testHandleThrowsException(): void
    {
        $exception = new \RuntimeException('Test');
        $handler = new DefaultExceptionHandler();

        $this->expectExceptionObject($exception);

        $handler->handle($exception);
    }

    public function testHandleNotThrowingException(): void
    {
        $exception = new \DomainException('Test');
        $handler = new DefaultExceptionHandler();

        $response = $handler->handle($exception);

        $this->assertInstanceOf(ExceptionDomainPayload::class, $response);
        $this->assertSame($exception, $response->getException());
    }
}
