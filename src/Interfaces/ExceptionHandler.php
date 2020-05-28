<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

use PayloadInterop\DomainPayload;
use Psr\Http\Message\ResponseInterface;

interface ExceptionHandler
{
    /**
     * @return DomainPayload|ResponseInterface
     */
    public function handle(\Throwable $e);
}
