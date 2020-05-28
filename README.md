# Polus.Adr

This is my implementation of [ADR](https://github.com/pmjones/adr)

## Installation

The preferred method of installing this library is with
[Composer](https://getcomposer.org/) by running the following from your project
root:

`
  $ composer require polus/adr
`

You will also need to include an implementation of a router and a middleware-dispatcher  

### Currently available routers

 - [polus/aura-router](https://github.com/polusphp/aura-router) - [Aura.Router](https://github.com/auraphp/Aura.Router)
 - [polus/fast-router](https://github.com/polusphp/fast-router) - [FastRoute](https://github.com/nikic/FastRoute)

### Currently available middleware dispatchers

 - [polus/ellipse-middleware-dispatcher](https://github.com/polusphp/ellipse-middleware-dispatcher) - [EllipsePHP](https://github.com/ellipsephp/)
 - [polus/relay-middleware-dispatcher](https://github.com/polusphp/relay-middleware-dispatcher) - [RelayPHP](http://relayphp.com/2.x)

## Old versions 

See [polus/polus-adr](https://github.com/polusphp/polus-adr) for version 1 and 2

## Example


```php
<?php


use Aura\Payload_Interface\PayloadInterface;use Aura\Router\RouterContainer;use Http\Factory\Diactoros\ResponseFactory;use Http\Factory\Diactoros\ServerRequestFactory;use Polus\Adr\Actions\AbstractDomainAction;use Polus\Adr\Adr;use Polus\Adr\Interfaces\Resolver;use Polus\Adr\ResponseHandler\HttpResponseHandler;use Polus\Router\AuraRouter\RouterCollection;use Polus\Router\RouterMiddleware;use Psr\Http\Message\ResponseInterface;use Psr\Http\Message\ServerRequestInterface;

$responseFactory = new \Http\Factory\Diactoros\ResponseFactory();
$routerContainer = new RouterContainer();
$routerCollection = new RouterCollection($routerContainer->getMap());
$routerDispatcher = new Polus\Router\AuraRouter\Dispatcher($routerContainer);

$actionResolver =  new class implements Resolver {
    //..
};

$adr = new Adr(
    new ResponseFactory(),
    $actionResolver,
    $routerCollection,
    new HttpResponseHandler(),
    new \Polus\MiddlewareDispatcher\Factory(
        new \Polus\MiddlewareDispatcher\Relay\Dispatcher($responseFactory),
        [
            new RouterMiddleware($routerDispatcher),
            //More psr-15 middlewares
        ]
    )
);

//Define routes and actions
class Responder implements Responder
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        PayloadInterface $payload
    ): ResponseInterface {
        $response->getBody()->write("Index responder\n");
        return $response;
    }
}

$adr->get('/', new class extends AbstractDomainAction {
    protected $responder = Responder::class;
});

//Run application
$factory = new ServerRequestFactory();
$adr->run($factory->createServerRequestFromArray($_SERVER));

```