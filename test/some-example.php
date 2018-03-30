<?php
/**
 * @var Inhere\Middleware\MiddlewareStack $stack
 */

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

function func_middle0(ServerRequestInterface $request, RequestHandlerInterface $handler)
{
    $leftVal = $leftVal = $request->getAttribute('test');
    $res = $handler->handle($request->withAttribute('test', $leftVal . 'func0 > '));
    $res->getBody()->write(' > func0');

    return $res;
}

function func_middle1(ServerRequestInterface $request, RequestHandlerInterface $handler)
{
    $leftVal = $leftVal = $request->getAttribute('test');
    $res = $handler->handle($request->withAttribute('test', $leftVal . 'func1 > '));
    $res->getBody()->write(' > func1');

    return $res;
}

class Middle0 implements \Psr\Http\Server\MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): \Psr\Http\Message\ResponseInterface {
        $leftVal = $leftVal = $request->getAttribute('test');
        $res = $handler->handle($request->withAttribute('test', $leftVal . 'class0 > '));
        $res->getBody()->write(' > class0');

        return $res;
    }
}

class Middle1 implements \Psr\Http\Server\MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): \Psr\Http\Message\ResponseInterface {
        $leftVal = $leftVal = $request->getAttribute('test');
        $res = $handler->handle($request->withAttribute('test', $leftVal . 'class1 > '));
        $res->getBody()->write(' > class1');

        return $res;
    }
}

class Middle2 implements \Psr\Http\Server\MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): \Psr\Http\Message\ResponseInterface {
        $leftVal = $leftVal = $request->getAttribute('test');
        $res = $handler->handle($request->withAttribute('test', $leftVal . 'class2 > '));
        $res->getBody()->write(' > class2');

        return $res;
    }
}

class Middle3 implements \Psr\Http\Server\MiddlewareInterface
{
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): \Psr\Http\Message\ResponseInterface {
        $leftVal = $leftVal = $request->getAttribute('test');
        $res = $handler->handle($request->withAttribute('test', $leftVal . 'class3 > '));
        $res->getBody()->write(' > class3');

        return $res;
    }
}

class Middle4
{
    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): \Psr\Http\Message\ResponseInterface {
        $leftVal = $leftVal = $request->getAttribute('test');
        $res = $handler->handle($request->withAttribute('test', $leftVal . 'class4 > '));
        $res->getBody()->write(' > class4');

        return $res;
    }
}

// use func, closure
$stack->add(
    'func_middle0',
    'func_middle1',
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $leftVal = $leftVal = $request->getAttribute('test');
        $res = $handler->handle($request->withAttribute('test', $leftVal . 'closure0 > '));
        $res->getBody()->write(' > closure0');

        return $res;
    },
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $leftVal = $leftVal = $request->getAttribute('test');
        $res = $handler->handle($request->withAttribute('test', $leftVal . 'closure1 > '));
        $res->getBody()->write(' > closure1');

        return $res;
    }
);

// use class
$stack->middle(Middle0::class);
$stack->middle(Middle1::class);

// use object, middleware class, callable class
$stack->use(
    new Middle2(),
    Middle3::class,
    Middle4::class
);
