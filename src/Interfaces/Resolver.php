<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

interface Resolver
{
    public function resolve(?string $class): callable;

    public function resolveResponder(?string $responder): Responder;
}
