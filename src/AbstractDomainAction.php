<?php declare(strict_types=1);

namespace Polus\Adr;

use Polus\Adr\Interfaces\Action;
use Polus\Adr\Interfaces\DomainAction;
use Polus\Adr\Interfaces\MiddlewareAwareAction;

abstract class AbstractDomainAction implements DomainAction, MiddlewareAwareAction
{
    protected ?string $input = null;
    protected ?string $responder = null;
    protected ?string $domain = null;
    protected array $middlewares = [];

    public function getInput(): ?string
    {
        return $this->input;
    }

    public function getResponder(): ?string
    {
        return $this->responder;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getWithoutMiddlewares(): Action
    {
        $clone = clone $this;
        $clone->middlewares = [];
        return $clone;
    }
}
