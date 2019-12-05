<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

use Polus\Adr\Interfaces\Action;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ActionDispatcher
{
    public function dispatch(Action $action, ServerRequestInterface $request): ResponseInterface;
}
