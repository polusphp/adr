<?php

namespace Polus\Tests\Adr\ResponseHandler;

use Nyholm\Psr7\Response;
use Polus\Adr\ResponseHandler\CliResponseHandler;
use PHPUnit\Framework\TestCase;

class ResponseHandlerTest extends TestCase
{
    public function testDefaultResponse(): void
    {
        $responseHandler = new CliResponseHandler();

        $response = new Response(
            200,
            [
                'X-Test' => '1',
            ],
            'test-body',
        );

        ob_start();
        $responseHandler->handle($response);
        $content = ob_get_clean();

        $this->assertSame('Headers: 
	X-Test: 1

Response status: 200
test-body', $content);
    }
}
