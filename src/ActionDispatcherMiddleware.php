<?php
declare(strict_types=1);

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
    private $responseFactory;
    private $actionDispatcher;

    public function __construct(ActionDispatcher $actionDispatcher, ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->actionDispatcher = $actionDispatcher;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getAttribute('route');
        if ($route instanceof RouteInterface && $route->getStatus() === RouterDispatcherInterface::FOUND) {
            return $this->actionDispatcher->dispatch($route->getHandler(), $request);
        }

        return $this->responseFactory->createResponse(404);
    }
}
