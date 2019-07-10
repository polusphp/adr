<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

interface RouteInterface
{
    public function getAction(): ActionInterface;
}
