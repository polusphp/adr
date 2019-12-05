<?php

namespace Polus\Adr\Interfaces;

use Polus\MiddlewareDispatcher\FactoryInterface as MiddlewareFactory;

interface ActionDispatcherFactory
{
    public function createActionDispatcher(MiddlewareFactory $middlewareFactory): ActionDispatcher;
}