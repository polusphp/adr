<?php declare(strict_types=1);

namespace Polus\Adr\ActionDispatcher;

use Aura\Payload\Payload;
use Polus\Adr\Interfaces\Action;
use Polus\Adr\Interfaces\ActionDispatcher;
use Polus\Adr\Interfaces\DomainAction;
use Polus\Adr\Interfaces\ExceptionHandler;
use Polus\Adr\Interfaces\Resolver;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class DomainActionDispatcher implements ActionDispatcher
{
    private Resolver $resolver;
    private ResponseFactoryInterface $responseFactory;
    private ExceptionHandler $exceptionHandler;

    public function __construct(
        Resolver $resolver,
        ResponseFactoryInterface $responseFactory,
        ExceptionHandler $exceptionHandler
    ) {
        $this->resolver = $resolver;
        $this->responseFactory = $responseFactory;
        $this->exceptionHandler = $exceptionHandler;
    }

    public function dispatch(Action $action, ServerRequestInterface $request): ResponseInterface
    {
        $payload = new Payload();
        try {
            $input = $this->resolver->resolve($action->getInput());
            $inputResponse = $input($request);
            if ($action instanceof DomainAction) {
                $domain = null;
                if ($action->getDomain()) {
                    $domain = $this->resolver->resolve($action->getDomain());
                    $payload = $domain($inputResponse);
                }
            }
        }
        catch (\Throwable $de) {
            $result = $this->exceptionHandler->handle($de);
            if ($result instanceof ResponseInterface) {
                return $result;
            }

            if ($result instanceof Payload) {
                $payload = $result->setInput($inputResponse ?? []);
            }
        }

        $responder = $this->resolver->resolveResponder($action->getResponder());
        return $responder($request, $this->responseFactory->createResponse(), $payload);
    }
}
