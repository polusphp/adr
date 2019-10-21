<?php

namespace Polus\Tests\Adr;

use Aura\Payload\Payload;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Polus\Adr\AbstractAction;
use Polus\Adr\ActionDispatcher;
use Polus\Adr\Interfaces\ResolverInterface;
use Polus\MiddlewareDispatcher\DispatcherInterface;
use Polus\MiddlewareDispatcher\FactoryInterface as MiddlewareFactoryInterface;
use Polus\MiddlewareDispatcher\Relay\Dispatcher;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ActionDispatcherTest extends TestCase
{
    private function getResolver(callable $responder): ResolverInterface
    {
        return new class($responder) implements ResolverInterface
        {
            private $responder;

            public function __construct(callable $responder)
            {
                $this->responder = $responder;
            }

            public function resolveDomain($domain): callable
            {
                return $domain;
            }

            public function resolveInput($input): callable
            {
                return $input;
            }

            public function resolveResponder($responder): callable
            {
                return $responder ?: $this->responder;
            }
        };
    }

    private function getMiddlewareFactory(): MiddlewareFactoryInterface
    {
        return new class implements MiddlewareFactoryInterface
        {
            public function newInstance(): DispatcherInterface
            {
                return new Dispatcher(new Psr17Factory());
            }

            public function newConfiguredInstance(?ContainerInterface $container = null): DispatcherInterface
            {
                return new Dispatcher(new Psr17Factory());
            }
        };
    }

    public function testEmptyAction(): void
    {
        $testResponse = new Response();

        $dispatcher = new ActionDispatcher(
            $this->getResolver(function () use ($testResponse) {
                return $testResponse;
            }),
            new Psr17Factory(),
            $this->getMiddlewareFactory()
        );

        $response = $dispatcher->dispatch(
            new class extends AbstractAction
            {
            },
            new ServerRequest('GET', '/')
        );

        $this->assertSame($testResponse, $response);
    }

    public function testWithMiddlewares(): void
    {
        $testResponse = new Response();
        $dispatcher = new ActionDispatcher(
            $this->getResolver(function () use ($testResponse) {
                return $testResponse;
            }),
            new Psr17Factory(),
            $this->getMiddlewareFactory()
        );

        $response = $dispatcher->dispatch(
            new class extends AbstractAction
            {
                public function __construct()
                {
                    $this->middlewares[] = new class implements MiddlewareInterface
                    {
                        public function process(
                            ServerRequestInterface $request,
                            RequestHandlerInterface $handler
                        ): ResponseInterface {
                            ActionDispatcherTest::assertTrue(true);

                            return $handler->handle($request);
                        }
                    };
                }
            },
            new ServerRequest('GET', '/')
        );

        $this->assertSame($testResponse, $response);
    }

    public function testInputAndDomain(): void
    {
        $testResponse = new Response();
        $dispatcher = new ActionDispatcher(
            $this->getResolver(static function () {
            }),
            new Psr17Factory(),
            $this->getMiddlewareFactory()
        );

        $testPayload = $this->createMock(Payload::class);
        $response = $dispatcher->dispatch(
            new class ($testPayload, $testResponse) extends AbstractAction
            {
                private $testPayload;
                private $testResponse;

                public function __construct($testPayload, $testResponse)
                {
                    $this->testPayload = $testPayload;
                    $this->testResponse = $testResponse;
                }

                public function getInput()
                {
                    return static function (ServerRequestInterface $request) {
                        return 'testInput';
                    };
                }

                public function getDomain()
                {
                    return function ($input) {
                        ActionDispatcherTest::assertSame('testInput', $input);
                        return $this->testPayload;
                    };
                }

                public function getResponder()
                {
                    return function ($request, $response, $payload) {
                        ActionDispatcherTest::assertSame($this->testPayload, $payload);
                        return $this->testResponse;
                    };
                }
            },
            new ServerRequest('GET', '/')
        );

        $this->assertSame($testResponse, $response);
    }

    public function testInputException(): void
    {
        $testResponse = new Response();

        $dispatcher = new ActionDispatcher(
            $this->getResolver(function () use ($testResponse) {
                return $testResponse;
            }),
            new Psr17Factory(),
            $this->getMiddlewareFactory()
        );

        $response = $dispatcher->dispatch(
            new class extends AbstractAction
            {
                public function getInput()
                {
                    return static function () {
                        throw new \DomainException('test');
                    };
                }

                public function getDomain()
                {
                    return static function () {
                    };
                }
            },
            new ServerRequest('GET', '/')
        );

        $this->assertSame($testResponse, $response);
    }

    public function testNextAction(): void
    {
        $testResponse = new Response();

        $dispatcher = new ActionDispatcher(
            $this->getResolver(function () use ($testResponse) {
                return $testResponse;
            }),
            new Psr17Factory(),
            $this->getMiddlewareFactory()
        );

        $response = $dispatcher->dispatch(
            new class extends AbstractAction
            {
                public function getInput()
                {
                    return static function (ServerRequestInterface $request) {
                        ActionDispatcherTest::assertFalse($request->getAttribute('currentResponse', false));

                        return 'test1';
                    };
                }

                public function getDomain()
                {
                    return static function () {
                    };
                }

                public function getNextAction()
                {
                    return new class extends AbstractAction
                    {
                        public function getInput()
                        {
                            return static function (ServerRequestInterface $request) {
                                ActionDispatcherTest::assertInstanceOf(ResponseInterface::class,
                                    $request->getAttribute('currentResponse'));

                                return 'test2';
                            };
                        }

                        public function getDomain()
                        {
                            return static function () {
                            };
                        }
                    };
                }
            },
            new ServerRequest('GET', '/')
        );

        $this->assertSame($testResponse, $response);
    }

    public function testInvalidResponder(): void
    {
        $testResponse = new Response();

        $dispatcher = new ActionDispatcher(
            $this->getResolver(function () use ($testResponse) {
                return $testResponse;
            }),
            new Psr17Factory(),
            $this->getMiddlewareFactory()
        );

        $this->expectException(\TypeError::class);

        $response = $dispatcher->dispatch(
            new class extends AbstractAction
            {
                public function getResponder()
                {
                    return 'error';
                }
            },
            new ServerRequest('GET', '/')
        );
    }
}
