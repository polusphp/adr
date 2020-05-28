<?php declare(strict_types=1);

namespace Polus\Adr;

use PayloadInterop\DomainPayload;
use PayloadInterop\DomainStatus;

final class EmptyDomainPayload implements DomainPayload
{
	public function getStatus(): string
	{
		return DomainStatus::SUCCESS;
	}

	public function getResult(): array
	{
		return [];
	}
}
