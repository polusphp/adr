<?php declare(strict_types=1);

namespace Polus\Adr\ResponseHandler;

use Polus\Adr\Interfaces\ResponseHandler;
use Psr\Http\Message\ResponseInterface;

class HttpResponseHandler implements ResponseHandler
{
    public const PASSTHRU = 'passthru';

    private ResponseInterface $response;

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function handle(ResponseInterface $response): void
    {
        $this->response = $response;

        $version = $response->getProtocolVersion();
        $status = $response->getStatusCode();
        $phrase = $response->getReasonPhrase();

        header("HTTP/{$version} {$status} {$phrase}");

        $doPassthru = false;

        foreach ($response->getHeaders() as $name => $values) {
            if ($name === self::PASSTHRU) {
                $doPassthru = true;
                continue;
            }
            $name = str_replace('-', ' ', $name);
            $name = ucwords($name);
            $name = str_replace(' ', '-', $name);
            foreach ($values as $value) {
                header("{$name}: {$value}", false);
            }
        }

        $stream = $response->getBody();

        if ($doPassthru) {
            fpassthru($stream->detach());
        }
        else {
            $stream->rewind();
            while (!$stream->eof()) {
                echo $stream->read(8192);
            }
        }
    }
}
