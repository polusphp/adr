<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

interface DomainAction extends Action
{
    public function getDomain(): ?string;
}
