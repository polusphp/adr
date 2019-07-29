<?php

namespace Polus\Adr;

use Polus\Adr\Interfaces\ActionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ActionDispatcherInterface
{
    public function dispatch(ActionInterface $action, ServerRequestInterface $request): ResponseInterface;
}
