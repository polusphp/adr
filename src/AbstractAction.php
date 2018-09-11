<?php
declare(strict_types=1);

namespace Polus\Adr;

use Polus\Adr\Interfaces\ActionInterface;

abstract class AbstractAction implements ActionInterface
{
    protected $input;
    protected $responder;
    protected $domain;
    protected $middlewares = [];


    /**
     * @return mixed
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return mixed
     */
    public function getResponder()
    {
        return $this->responder;
    }

    /**
     * @return mixed
     */
    public function getDomain()
    {
        return $this->domain;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getWithoutMiddlewares(): ActionInterface
    {
        $clone = clone $this;
        $clone->middlewares = [];
        return $clone;
    }
}