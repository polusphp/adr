<?php
declare(strict_types=1);

namespace Polus\Adr\Interfaces;

interface ActionInterface
{
    public function getInput();
    public function getResponder();
    public function getDomain();
    public function getMiddlewares(): array;

    public function getWithoutMiddlewares(): ActionInterface;
}