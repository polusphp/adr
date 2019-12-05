<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

interface MiddlewareAwareAction
{
    public function getMiddlewares(): array;
    public function getWithoutMiddlewares(): Action;
}
