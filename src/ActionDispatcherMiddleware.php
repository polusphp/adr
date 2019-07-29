<?php declare(strict_types=1);

namespace Polus\Adr;

use Polus\Router\RouteInterface;
use Polus\Router\RouterDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ActionDispatcherMiddleware implements MiddlewareInterface
{
    /** @var ResponseFactoryInterface */
    private $responseFactory;
    /** @var ActionDispatcherInterface */
    private $actionDispatcher;

    public function __construct(ActionDispatcherInterface $actionDispatcher, ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->actionDispatcher = $actionDispatcher;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute('route');
        if ($route instanceof RouteInterface && $route->getStatus() === RouterDispatcherInterface::FOUND) {
            return $this->actionDispatcher->dispatch($route->getHandler(), $request);
        }
        if ($route instanceof RouteInterface && $route->getStatus() === RouterDispatcherInterface::METHOD_NOT_ALLOWED) {
            return $this->responseFactory->createResponse(405);
        }

        return $this->responseFactory->createResponse(404);
    }
}
