<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface ResponseHandler
{
    public function getResponse(): ResponseInterface;
    
    public function handle(ResponseInterface $response): void;
}
