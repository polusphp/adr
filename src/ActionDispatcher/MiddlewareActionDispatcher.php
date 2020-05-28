<?php declare(strict_types=1);

namespace Polus\Adr\ActionDispatcher;

use Polus\Adr\Interfaces\ActionDispatcher;
use Polus\Adr\Interfaces\Action;
use Polus\Adr\Interfaces\MiddlewareAwareAction;
use Polus\MiddlewareDispatcher\FactoryInterface as MiddlewareFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class MiddlewareActionDispatcher implements ActionDispatcher
{
    private MiddlewareFactory $middlewareFactory;
    private ActionDispatcher $actionDispatcher;

    public function __construct(
        ActionDispatcher $actionDispatcher,
        MiddlewareFactory $middlewareFactory
    ) {
        $this->middlewareFactory = $middlewareFactory;
        $this->actionDispatcher = $actionDispatcher;
    }

    public function dispatch(Action $action, ServerRequestInterface $request): ResponseInterface
    {
        if ($action instanceof MiddlewareAwareAction && \count($action->getMiddlewares())) {
            $middlewareDispatcher = $this->middlewareFactory->newInstance();
            $middlewareDispatcher->addMiddlewares($action->getMiddlewares());

            $doAction = function ($request) use ($action) {
                return $this->actionDispatcher->dispatch($action->getWithoutMiddlewares(), $request);
            };

            $actionHandler = new class ($doAction) implements MiddlewareInterface
            {
                /** @var callable */
                private $runAction;

                public function __construct(callable $runAction)
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
