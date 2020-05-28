<?php declare(strict_types=1);

namespace Polus\Tests\Adr;

use PayloadInterop\DomainStatus;
use Polus\Adr\EmptyDomainPayload;
use PHPUnit\Framework\TestCase;

class EmptyDomainPayloadTest extends TestCase
{
    public function test(): void
    {
        $payload = new EmptyDomainPayload();
        $this->assertSame(DomainStatus::SUCCESS, $payload->getStatus());
        $this->assertSame([], $payload->getResult());
    }
}
