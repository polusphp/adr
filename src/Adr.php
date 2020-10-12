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

/**
 * @method void get(string $route, Action $handler);
 * @method void put(string $route, Action $handler);
 * @method void post(string $route, Action $handler);
 * @method void delete(string $route, Action $handler);
 * @method void patch(string $route, Action $handler);
 * @method void head(string $route, Action $handler);
 * @method void attach(string $prefix, callable $callback);
 */
class Adr implements RequestHandlerInterface
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
}
