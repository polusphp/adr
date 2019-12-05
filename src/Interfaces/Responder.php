<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

use Aura\Payload_Interface\PayloadInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface Responder
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ): ResponseInterface;
}
