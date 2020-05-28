<?php declare(strict_types=1);

namespace Polus\Tests\Adr\Fixture;

use PayloadInterop\DomainPayload;
use PayloadInterop\DomainStatus;

final class TestDomainPayload implements DomainPayload
{
    public function getStatus(): string
    {
        return DomainStatus::SUCCESS;
    }

    public function getResult(): array
    {
        return ['test' => true];
    }
}
