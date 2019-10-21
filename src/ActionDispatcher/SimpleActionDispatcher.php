<?php declare(strict_types=1);

namespace Polus\Adr\ActionDispatcher;

use Aura\Payload\Payload;
use Aura\Payload_Interface\PayloadInterface;
use Aura\Payload_Interface\PayloadStatus;
use Polus\Adr\ActionDispatcherInterface;
use Polus\Adr\Interfaces\ActionInterface;
use Polus\Adr\Interfaces\ResolverInterface;
use Polus\Adr\Interfaces\ResponderInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class SimpleActionDispatcher implements ActionDispatcherInterface
{
    /** @var ResolverInterface */
    private $resolver;
    /** @var ResponseFactoryInterface */
    private $responseFactory;

    public function __construct(
        ResolverInterface $resolver,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->resolver = $resolver;
        $this->responseFactory = $responseFactory;
    }

    public function dispatch(ActionInterface $action, ServerRequestInterface $request): ResponseInterface
    {
        $domain = null;
        if ($action->getDomain()) {
            $domain = $this->resolver->resolveDomain($action->getDomain());
        }

        if (\is_callable($domain)) {
            try {
                $input = $this->resolver->resolveInput($action->getInput());
                $payload = $domain($input($request));
            }
            catch (\DomainException $de) {
                $result = $this->handleInputException($de);
                if ($result instanceof ResponseInterface) {
                    return $result;
                }

                if ($result instanceof Payload) {
                    //todo refactor how we set input
                    $payload = $result->setInput($input ?? []);
                }
            }
        }
        else {
            $payload = new Payload();
            $payload->setStatus(PayloadStatus::SUCCESS);
        }

        $responder = $this->resolver->resolveResponder($action->getResponder());
        if ($responder instanceof ResponderInterface || \is_callable($responder)) {
            $response = $responder($request, $this->responseFactory->createResponse(), $payload);

            if (method_exists($action, 'getNextAction')) {
                $newAction = $action->getNextAction();
                if ($newAction) {
                    $request = $request->withAttribute('currentResponse', $response);
                    $this->dispatch($newAction, $request);
                }
            }

            return $response;
        }
        throw new \RuntimeException('Invalid responder. Responder must implement ResponderInterface or be callable');
    }

    /**
     * @param \Throwable $e
     * @return PayloadInterface|ResponseInterface
     */
    protected function handleInputException(\Throwable $e)
    {
        $payload = new Payload();
        $payload->setStatus(PayloadStatus::FAILURE);
        $payload->setMessages($e->getMessage());
        $payload->setOutput([
            'exception' => [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]
        ]);
        $payload->setExtras([
            'exception' => $e
        ]);

        return $payload;
    }

    protected function getResponseFactory(): ResponseFactoryInterface
    {
        return $this->responseFactory;
    }
}
