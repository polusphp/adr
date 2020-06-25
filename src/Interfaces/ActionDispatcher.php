<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ActionDispatcher
{
    public function dispatch(Action $action, ServerRequestInterface $request): ResponseInterface;
}
