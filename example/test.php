<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/20
 * Time: 下午10:04
 */

use Inhere\Http\HttpFactory;
use Inhere\Middleware\MiddlewareStack;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require dirname(__DIR__) . '/../../autoload.php';

function func_middleware($request, RequestHandlerInterface $handler)
{
    echo ">>> 0 before\n";
    $res = $handler->handle($request);
    echo "0 after >>>\n";

    return $res;
}

function func_middleware1($request, RequestHandlerInterface $handler)
{
    echo ">>> n before \n";
    $res = $handler->handle($request);
    echo "n after >>>\n";

    return $res;
}

$chain = new MiddlewareStack([
    'func_middleware',
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        echo ">>> 1 before \n";
        $res = $handler->handle($request);
        $res->getBody()->write(' + node 1');
        echo "1 after >>> \n";

        return $res;
    },
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        echo ">>> 2 before \n";
        $res = $handler->handle($request);
        $res->getBody()->write(' + node 2');
        echo "2 after >>> \n";

        return $res;
    },
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        echo ">>> 3 before \n";
        $res = $handler->handle($request);
        $res->getBody()->write(' + node 3');
        echo "3 after >>> \n";

        return $res;
    },
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
       // $res = HttpFactory::createResponse();
       // $res->getBody()->write('content');

        echo ">>> 4 before \n";
        $res = $handler->handle($request);
        $res->getBody()->write('node 4');
        echo "4 after >>> \n";

        return $res;
    },
    'func_middleware1'
]);

$request = HttpFactory::createServerRequest('GET', 'http://www.abc.com/home');

$chain->setCoreHandler(function () {
    echo " (THIS IS CORE)\n";
    $res = HttpFactory::createResponse();
    $res->getBody()->write(' (CORE) ');

    return $res;
});

$res = $chain($request);

echo PHP_EOL . 'response content:', (string)$res->getBody() . PHP_EOL;

//var_dump($chain);

/*
OUTPUT:
$ php examples/test.php
>>> 0 before
>>> 1 before
>>> 2 before
>>> 3 before
>>> 4 before
>>> n before
 (THIS IS CORE)
n after >>>
4 after >>>
3 after >>>
2 after >>>
1 after >>>
0 after >>>

response content: node 4 + node 3 + node 2 + node 1

 */
