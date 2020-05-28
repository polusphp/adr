<?php

namespace Polus\Tests\Adr;

use PayloadInterop\DomainStatus;
use Polus\Adr\ExceptionDomainPayload;
use PHPUnit\Framework\TestCase;

class ExceptionDomainPayloadTest extends TestCase
{
    public function test(): void
    {
        $exception = new \Exception('Test', 1);

        $payload = new ExceptionDomainPayload($exception);

        $this->assertSame($exception, $payload->getException());

        $this->assertSame(DomainStatus::ERROR, $payload->getStatus());

        $this->assertSame($exception->getMessage(), $payload->getResult()['exception']['message']);
        $this->assertSame($exception->getCode(), $payload->getResult()['exception']['code']);
    }
}
