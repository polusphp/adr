<?php declare(strict_types=1);

namespace Polus\Adr\Actions;

use Polus\Adr\Interfaces\Action;
use Polus\Adr\Interfaces\MiddlewareAwareAction;

abstract class AbstractAction implements Action, MiddlewareAwareAction
{
    protected ?string $input = null;
    protected ?string $responder = null;
    protected array $middlewares = [];

    public function getInput(): ?string
    {
        return $this->input;
    }

    public function getResponder(): ?string
    {
        return $this->responder;
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
