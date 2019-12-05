<?php declare(strict_types=1);

namespace Polus\Adr;

use Polus\Adr\ActionDispatcher\MiddlewareActionDispatcher;
use Polus\Adr\ActionDispatcher\DomainActionDispatcher;
use Polus\Adr\Interfaces\ActionDispatcher;
use Polus\Adr\Interfaces\ActionDispatcherFactory;
use Polus\Adr\Interfaces\Resolver;
use Polus\MiddlewareDispatcher\FactoryInterface as MiddlewareFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class DefaultActionDispatcherFactory implements ActionDispatcherFactory
{
    private ResponseFactoryInterface $responseFactory;
    private Resolver $actionResolver;

    public function __construct(Resolver $actionResolver, ResponseFactoryInterface $responseFactory)
    {
        $this->actionResolver = $actionResolver;
        $this->responseFactory = $responseFactory;
    }

    public function createActionDispatcher(MiddlewareFactoryInterface $middlewareFactory): ActionDispatcher
    {
        return new MiddlewareActionDispatcher(
            new DomainActionDispatcher($this->actionResolver, $this->responseFactory, new DefaultExceptionHandler()),
            $middlewareFactory
        );
    }
}
