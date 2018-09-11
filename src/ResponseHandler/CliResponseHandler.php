<?php
declare(strict_types=1);

namespace Polus\Adr\ResponseHandler;

use Polus\Adr\Interfaces\ResponseHandlerInterface;
use Psr\Http\Message\ResponseInterface;

class CliResponseHandler implements ResponseHandlerInterface
{
    /** @var ResponseInterface */
    private $response;

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function handle(ResponseInterface $response): void
    {
        $this->response = $response;

        if (\count($response->getHeaders())) {
            echo "Headers: \n";
            foreach ($response->getHeaders() as $header => $values) {
                echo "\t$header: ".implode(', ', $values)."\n";
            }
        }

        echo "\nResponse status: " . $response->getStatusCode()."\n";

        $stream = $response->getBody();
        $stream->rewind();
        while (! $stream->eof()) {
            echo $stream->read(8192);
        }
    }
}
