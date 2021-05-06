<?php declare(strict_types=1);

namespace Polus\Adr\Actions;

use Polus\Adr\Interfaces\DomainAction;

abstract class AbstractDomainAction extends AbstractAction implements DomainAction
{
    protected ?string $domain = null;

    public function getDomain(): ?string
    {
        return $this->domain;
    }
}
