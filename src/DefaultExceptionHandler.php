<?php declare(strict_types=1);

namespace Polus\Adr;

use Aura\Payload\Payload;
use Aura\Payload_Interface\PayloadInterface;
use Aura\Payload_Interface\PayloadStatus;
use Polus\Adr\Interfaces\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;

final class DefaultExceptionHandler implements ExceptionHandler
{
    /**
     * @return PayloadInterface|ResponseInterface
     */
    public function handle(\Throwable $e)
    {
        if (!$e instanceof \DomainException && !$e instanceof \InvalidArgumentException) {
            throw $e;
        }
        $payload = new Payload();
        $payload->setStatus(PayloadStatus::FAILURE);
        $payload->setMessages($e->getMessage());
        $payload->setOutput([
            'exception' => [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]
        ]);
        $payload->setExtras([
            'exception' => $e
        ]);

        return $payload;
    }
}
