<?php declare(strict_types=1);

namespace Polus\Adr\ActionDispatcher;

use Polus\Adr\ActionDispatcherInterface;
use Polus\Adr\Interfaces\ActionInterface;
use Polus\MiddlewareDispatcher\FactoryInterface as MiddlewareFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewareActionDispatcher implements ActionDispatcherInterface
{
    /** @var MiddlewareFactoryInterface */
    private $middlewareFactory;
    /** @var ActionDispatcherInterface */
    private $actionDispatcher;

    public function __construct(
        ActionDispatcherInterface $actionDispatcher,
        MiddlewareFactoryInterface $middlewareFactory
    ) {
        $this->middlewareFactory = $middlewareFactory;
        $this->actionDispatcher = $actionDispatcher;
    }

    public function dispatch(ActionInterface $action, ServerRequestInterface $request): ResponseInterface
    {
        if (\count($action->getMiddlewares())) {
            $middlewareDispatcher = $this->middlewareFactory->newInstance();
            $middlewareDispatcher->addMiddlewares($action->getMiddlewares());

            $doAction = function ($request) use ($action) {
                return $this->actionDispatcher->dispatch($action->getWithoutMiddlewares(), $request);
            };

            $actionHandler = new class ($doAction) implements MiddlewareInterface
            {
                private $runAction;

                public function __construct($runAction)
                {
                    $this->runAction = $runAction;
                }

                public function process(
                    ServerRequestInterface $request,
                    RequestHandlerInterface $handler
                ): ResponseInterface {
                    $action = $this->runAction;
                    return $action($request);
                }
            };

            $middlewareDispatcher->addMiddleware($actionHandler);

            return $middlewareDispatcher->dispatch($request);
        }

        return $this->actionDispatcher->dispatch($action, $request);
    }
}
