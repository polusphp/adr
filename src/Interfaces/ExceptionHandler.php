<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

use Aura\Payload_Interface\PayloadInterface;
use Psr\Http\Message\ResponseInterface;

interface ExceptionHandler
{
    /**
     * @return PayloadInterface|ResponseInterface
     */
    public function handle(\Throwable $e);
}
