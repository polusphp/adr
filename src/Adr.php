<?php declare(strict_types=1);

namespace Polus\Adr;

use Polus\Adr\ActionDispatcher\HandlerActionDispatcher;
use Polus\Adr\ActionDispatcher\MiddlewareActionDispatcher;
use Polus\Adr\Interfaces\Action;
use Polus\Adr\Interfaces\ActionDispatcher;
use Polus\Adr\Interfaces\Resolver;
use Polus\Adr\Interfaces\ResponseHandler;
use Polus\Adr\Middleware\ActionDispatcherMiddleware;
use Polus\MiddlewareDispatcher\FactoryInterface as MiddlewareFactory;
use Polus\Router\RouterCollection;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Adr implements RequestHandlerInterface, RouterCollection
{
    private ResponseFactoryInterface $responseFactory;
    private MiddlewareFactory $middlewareFactory;
    private ResponseHandler $responseHandler;
    private RouterCollection $routerContainer;
    private ActionDispatcher $actionDispatcher;

    public function __construct(
        ResponseFactoryInterface $responseFactory,
        Resolver $actionResolver,
        RouterCollection $routerContainer,
        ResponseHandler $responseHandler,
        MiddlewareFactory $middlewareFactory,
        ?ActionDispatcher $actionDispatcher = null
    ) {
        $this->responseFactory = $responseFactory;
        $this->middlewareFactory = $middlewareFactory;
        $this->responseHandler = $responseHandler;
        $this->routerContainer = $routerContainer;
        $this->actionDispatcher = $actionDispatcher ?? new MiddlewareActionDispatcher(
            HandlerActionDispatcher::default($actionResolver, $responseFactory),
            $this->middlewareFactory,
        );
    }

    public function getRouter(): RouterCollection
    {
        return $this->routerContainer;
    }

    public function run(ServerRequestInterface $request): ResponseHandler
    {
        $this->responseHandler->handle($this->handle($request));
        return $this->responseHandler;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middlewareDispatcher = $this->middlewareFactory->newConfiguredInstance();
        $middlewareDispatcher->addMiddleware(
            new ActionDispatcherMiddleware(
                $this->actionDispatcher,
                $this->responseFactory,
            )
        );

        return $middlewareDispatcher->dispatch($request);
    }

    protected function routerContainerProxy(string $method, ...$args)
    {
        $this->routerContainer->$method(...$args);
    }

    public function get(string $route, $handler)
    {
        $this->routerContainerProxy('get', $route, $handler);
    }

    public function put(string $route, $handler)
    {
        $this->routerContainerProxy('put', $route, $handler);
    }

    public function post(string $route, $handler)
    {
        $this->routerContainerProxy('post', $route, $handler);
    }

    public function delete(string $route, $handler)
    {
        $this->routerContainerProxy('delete', $route, $handler);
    }

    public function patch(string $route, $handler)
    {
        $this->routerContainerProxy('patch', $route, $handler);
    }

    public function head(string $route, $handler)
    {
        $this->routerContainerProxy('head', $route, $handler);
    }

    public function attach(string $prefix, callable $callback)
    {
        $this->routerContainerProxy('attach', $prefix, $callback);
    }
}
