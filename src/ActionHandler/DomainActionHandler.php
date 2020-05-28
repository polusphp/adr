<?php declare(strict_types=1);

namespace Polus\Adr\ActionHandler;

use PayloadInterop\DomainPayload;
use Polus\Adr\Interfaces\Action;
use Polus\Adr\Interfaces\DomainAction;
use Polus\Adr\Interfaces\Resolver;
use Psr\Http\Message\ServerRequestInterface;

final class DomainActionHandler implements Handler
{
    private Resolver $resolver;

    public function __construct(Resolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function support(Action $action): bool
    {
        return $action instanceof DomainAction;
    }

    public function handle(Action $action, ServerRequestInterface $request): ?DomainPayload
    {
        if (!$action instanceof DomainAction) {
            return null;
        }

        if ($action->getDomain()) {
            $inputResponse = null;
            if ($action->getInput()) {
                $input = $this->resolver->resolve($action->getInput());
                $inputResponse = $input($request);
            }
            $domain = $this->resolver->resolve($action->getDomain());
            return $domain($inputResponse);
        }

        return null;
    }
}
