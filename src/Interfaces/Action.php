<?php declare(strict_types=1);

namespace Polus\Adr\Interfaces;

interface Action
{
    public function getInput(): ?string;
    public function getResponder(): ?string;
}
