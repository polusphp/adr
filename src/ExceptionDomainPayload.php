<?php declare(strict_types=1);

namespace Polus\Adr;

use PayloadInterop\DomainPayload;
use PayloadInterop\DomainStatus;

final class ExceptionDomainPayload implements DomainPayload
{
    private \Throwable $exception;

    public function __construct(\Throwable $exception)
    {
        $this->exception = $exception;
    }

    public function getException(): \Throwable
    {
        return $this->exception;
    }

    public function getStatus(): string
    {
        return DomainStatus::ERROR;
    }

    public function getResult(): array
    {
        return [
            'exception' => [
                'message' => $this->exception->getMessage(),
                'code' => $this->exception->getCode(),
            ]
        ];
    }
}
