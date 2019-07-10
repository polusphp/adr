<?php declare(strict_types=1);

namespace Polus\Adr;

use Polus\Adr\Interfaces\ActionInterface;
use Polus\Adr\Interfaces\ResolverInterface;
use Polus\Adr\Interfaces\ResponseHandlerInterface;
use Polus\Router\RouterCollectionInterface;
use Polus\MiddlewareDispatcher\FactoryInterface as MiddlewareFactoryInterface;
use Polus\MiddlewareDispatcher\DispatcherInterface as MiddlewareDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class App
 * @package Polus\Adr
 *
 * @method void get(string $route, ActionInterface $handler);
 * @method void put(string $route, ActionInterface $handler);
 * @method void post(string $route, ActionInterface $handler);
 * @method void delete(string $route, ActionInterface $handler);
 * @method void patch(string $route, ActionInterface $handler);
 * @method void head(string $route, ActionInterface $handler);
 * @method void attach(string $prefix, callable $callback);
 */
class Adr
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;

    /** @var MiddlewareDispatcherInterface */
    private $middlewareDispatcher;

    /** @var ResolverInterface */
    private $actionResolver;

    /** @var MiddlewareFactoryInterface */
    private $middlewareFactory;

    /** @var ResponseHandlerInterface */
    private $responseHandler;

    /** @var RouterCollectionInterface */
    private $routerContainer;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        ResolverInterface $actionResolver,
        RouterCollectionInterface $routerContainer,
        ResponseHandlerInterface $responseHandler,
        MiddlewareFactoryInterface $middlewareFactory
    ) {
        $this->responseFactory = $responseFactory;
        $this->actionResolver = $actionResolver;
        $this->middlewareFactory = $middlewareFactory;
        $this->responseHandler = $responseHandler;
        $this->routerContainer = $routerContainer;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->routerContainer, $name)) {
            $this->routerContainer->$name(...$arguments);
        }
    }

    public function run(ServerRequestInterface $request): ResponseHandlerInterface
    {
        $this->middlewareDispatcher = $this->middlewareFactory->newConfiguredInstance();
        $this->middlewareDispatcher->addMiddleware(
            new ActionDispatcherMiddleware(
                new ActionDispatcher(
                    $this->actionResolver,
                    $this->responseFactory,
                    $this->middlewareFactory
                ),
                $this->responseFactory
            )
        );
        $this->responseHandler->handle($this->middlewareDispatcher->dispatch($request));
        return $this->responseHandler;
    }
}
