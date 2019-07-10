<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

interface ResolverInterface
{
    public function resolveDomain($domain): callable;
    public function resolveInput($input): callable;
    public function resolveResponder($responder): callable;
}
