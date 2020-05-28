<?php declare(strict_types=1);

namespace Polus\Adr\Middleware;

use Polus\Adr\Interfaces\ActionDispatcher;
use Polus\Router\Route;
use Polus\Router\RouterDispatcher;
use Polus\Router\RouterMiddleware;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ActionDispatcherMiddleware implements MiddlewareInterface
{
    private ResponseFactoryInterface $responseFactory;
    private ActionDispatcher $actionDispatcher;

    public function __construct(ActionDispatcher $actionDispatcher, ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->actionDispatcher = $actionDispatcher;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute(RouterMiddleware::ATTRIBUTE_KEY);
        if ($route instanceof Route && $route->getStatus() === RouterDispatcher::FOUND) {
            return $this->actionDispatcher->dispatch($route->getHandler(), $request);
        }
        if ($route instanceof Route && $route->getStatus() === RouterDispatcher::METHOD_NOT_ALLOWED) {
            return $this->responseFactory->createResponse(405);
        }

        return $this->responseFactory->createResponse(404);
    }
}
