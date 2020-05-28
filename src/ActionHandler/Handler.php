<?php declare(strict_types=1);

namespace Polus\Adr\ActionHandler;

use PayloadInterop\DomainPayload;
use Polus\Adr\Interfaces\Action;
use Psr\Http\Message\ServerRequestInterface;

interface Handler
{
    public function support(Action $action): bool;

    public function handle(Action $action, ServerRequestInterface $request): ?DomainPayload;
}
