<?php declare(strict_types=1);

namespace Polus\Adr;

use Polus\Adr\Interfaces\Action;
use Polus\Adr\Interfaces\Resolver;
use Polus\Adr\Interfaces\ResponseHandler;
use Polus\Adr\Middleware\ActionDispatcherMiddleware;
use Polus\Router\RouterCollection;
use Polus\MiddlewareDispatcher\FactoryInterface as MiddlewareFactory;
use Polus\MiddlewareDispatcher\DispatcherInterface as MiddlewareDispatcher;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @method void get(string $route, Action $handler);
 * @method void put(string $route, Action $handler);
 * @method void post(string $route, Action $handler);
 * @method void delete(string $route, Action $handler);
 * @method void patch(string $route, Action $handler);
 * @method void head(string $route, Action $handler);
 * @method void attach(string $prefix, callable $callback);
 */
class Adr
{
    private ResponseFactoryInterface $responseFactory;
    private MiddlewareDispatcher $middlewareDispatcher;
    private MiddlewareFactory $middlewareFactory;
    private ResponseHandler $responseHandler;
    private RouterCollection $routerContainer;
    private DefaultActionDispatcherFactory $actionDispatcherFactory;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        Resolver $actionResolver,
        RouterCollection $routerContainer,
        ResponseHandler $responseHandler,
        MiddlewareFactory $middlewareFactory,
        ?DefaultActionDispatcherFactory $actionDispatcher = null
    ) {
        $this->responseFactory = $responseFactory;
        $this->middlewareFactory = $middlewareFactory;
        $this->responseHandler = $responseHandler;
        $this->routerContainer = $routerContainer;
        $this->actionDispatcherFactory = $actionDispatcher ?? new DefaultActionDispatcherFactory($actionResolver, $responseFactory);
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->routerContainer, $name)) {
            $this->routerContainer->$name(...$arguments);
        }
    }

    public function getRouter(): RouterCollection
    {
        return $this->routerContainer;
    }

    public function run(ServerRequestInterface $request): ResponseHandler
    {
        $this->middlewareDispatcher = $this->middlewareFactory->newConfiguredInstance();
        $this->middlewareDispatcher->addMiddleware(
            new ActionDispatcherMiddleware(
                $this->actionDispatcherFactory->createActionDispatcher($this->middlewareFactory),
                $this->responseFactory
            )
        );
        $this->responseHandler->handle($this->middlewareDispatcher->dispatch($request));
        return $this->responseHandler;
    }
}
