<?php declare(strict_types=1);

namespace Polus\Adr;

use Polus\Adr\Interfaces\ResolverInterface;
use Polus\MiddlewareDispatcher\FactoryInterface as MiddlewareFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

final class ActionDispatcherFactory
{
    /** @var ResolverInterface */
    private $actionResolver;
    /** @var callable|null */
    private $dispatcherFactory;
    /** @var ResponseFactoryInterface */
    private $responseFactory;
    /** @var MiddlewareFactoryInterface */
    private $middlewareFactory;

    public function __construct(
        ResolverInterface $actionResolver,
        ResponseFactoryInterface $responseFactory,
        callable $dispatcherFactory = null
    ) {
        $this->actionResolver = $actionResolver;
        $this->dispatcherFactory = $dispatcherFactory;
        $this->responseFactory = $responseFactory;
    }

    public function createActionDispatcher(MiddlewareFactoryInterface $middlewareFactory): ActionDispatcherInterface
    {
        if ($this->dispatcherFactory) {
            $factory = $this->dispatcherFactory;
            return $factory($this->actionResolver, $this->responseFactory, $middlewareFactory);
        }
        return new ActionDispatcher(
            $this->actionResolver,
            $this->responseFactory,
            $middlewareFactory
        );
    }
}
