<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

use PayloadInterop\DomainPayload;

interface PayloadAware
{
	public function getPayload(): DomainPayload;
}
