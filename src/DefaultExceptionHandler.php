<?php declare(strict_types=1);

namespace Polus\Adr;

use PayloadInterop\DomainPayload;
use Polus\Adr\Interfaces\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;

final class DefaultExceptionHandler implements ExceptionHandler
{
    /**
     * @return DomainPayload|ResponseInterface
     */
    public function handle(\Throwable $e)
    {
        if (!$e instanceof \DomainException && !$e instanceof \InvalidArgumentException) {
            throw $e;
        }
        return new ExceptionDomainPayload($e);
    }
}
