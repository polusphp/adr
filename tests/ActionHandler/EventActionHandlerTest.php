<?php

namespace Polus\Tests\Adr\ActionHandler;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use PayloadInterop\DomainPayload;
use PHPUnit\Framework\TestCase;
use Polus\Adr\ActionHandler\EventActionHandler;
use Polus\Adr\Actions\AbstractAction;
use Polus\Adr\Actions\AbstractDomainAction;
use Polus\Adr\DefaultExceptionHandler;
use Polus\Adr\EmptyDomainPayload;
use Polus\Adr\ExceptionDomainPayload;
use Polus\Adr\Interfaces\ActionDispatcher;
use Polus\Adr\Interfaces\EventAction;
use Polus\Adr\Interfaces\PayloadAware;
use Polus\Adr\Interfaces\Resolver;
use Polus\Adr\Interfaces\Responder;
use Polus\Tests\Adr\Fixture\TestDomainPayload;
use Psr\EventDispatcher\EventDispatcherInterface;

class EventActionHandlerTest extends TestCase
{
    public function testMissingInput(): void
    {
        $resolver = $this->createMock(Resolver::class);
        $resolver
            ->method('resolveResponder')
            ->willReturn($this->createResponder(ExceptionDomainPayload::class));
        $resolver
            ->method('resolve')
            ->willThrowException(new \DomainException());

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $dispatcher = $this->createActionDispatcher($resolver, $eventDispatcher);

        $dispatcher->dispatch(
            new class extends AbstractAction implements EventAction {},
            new ServerRequest('GET', '/')
        );
    }

    public function testInputReturnNull(): void
    {
        $inputClass = new class () {
            public function __invoke()
            {
                return null;
            }
        };
        $responder = $this->createMock(Responder::class);
        $responder->expects($this->never())->method('__invoke');
        $resolver = $this->createMock(Resolver::class);
        $resolver
            ->method('resolveResponder')
            ->willReturn($responder);
        $resolver
            ->method('resolve')
            ->willReturn($inputClass);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->never())
            ->method('dispatch');

        $dispatcher = $this->createActionDispatcher($resolver, $eventDispatcher);

        $this->expectException(\TypeError::class);

        $dispatcher->dispatch(
            new class extends AbstractAction implements EventAction {
                protected ?string $input = 'test';
            },
            new ServerRequest('GET', '/')
        );
    }

    public function testInputReturnPayloadAware(): void
    {
        $event = new class() implements PayloadAware {
            public function getPayload(): DomainPayload
            {
                return new TestDomainPayload();
            }
        };
        $inputClass = new class ($event) {
            private $event;

            public function __construct($event)
            {
                $this->event = $event;
            }

            public function __invoke()
            {
                return $this->event;
            }
        };

        $resolver = $this->createMock(Resolver::class);
        $resolver
            ->method('resolveResponder')
            ->willReturn($this->createResponder(TestDomainPayload::class));
        $resolver
            ->method('resolve')
            ->willReturn($inputClass);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($event)
            ->willReturn($event);

        $dispatcher = $this->createActionDispatcher($resolver, $eventDispatcher);

        $dispatcher->dispatch(
            new class extends AbstractAction implements EventAction {
                protected ?string $input = 'test';
            },
            new ServerRequest('GET', '/')
        );
    }

    public function testNormal(): void
    {
        $event = new class() {};
        $inputClass = new class ($event) {
            private $event;

            public function __construct($event)
            {
                $this->event = $event;
            }

            public function __invoke()
            {
                return $this->event;
            }
        };

        $resolver = $this->createMock(Resolver::class);
        $resolver
            ->method('resolveResponder')
            ->willReturn($this->createResponder(EmptyDomainPayload::class));
        $resolver
            ->method('resolve')
            ->willReturn($inputClass);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($event);

        $dispatcher = $this->createActionDispatcher($resolver, $eventDispatcher);

        $dispatcher->dispatch(
            new class extends AbstractAction implements EventAction {
                protected ?string $input = 'test';
            },
            new ServerRequest('GET', '/')
        );
    }

    private function createResponder(string $expectedDomainPayload): Responder
    {
        $responder = $this->createMock(Responder::class);
        $responder
            ->expects($this->once())
            ->method('__invoke')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->isInstanceOf($expectedDomainPayload)
            );
        return $responder;
    }

    private function createActionDispatcher(
        Resolver $resolver,
        EventDispatcherInterface $eventDispatcher
    ): ActionDispatcher {
        $dispatcher = \Polus\Adr\ActionDispatcher\HandlerActionDispatcher::default(
            $resolver,
            new Psr17Factory(),
        );
        $dispatcher->addHandler(new EventActionHandler($resolver, $eventDispatcher));
        return $dispatcher;
    }
}
