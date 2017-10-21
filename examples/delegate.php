<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/20
 * Time: 下午9:55
 */


use Inhere\Http\HttpFactory;
use Inhere\Middleware\Delegate;
use Inhere\Middleware\MiddlewareChain;
use Inhere\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

require dirname(__DIR__) . '/../../autoload.php';

$chain = new Delegate([
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
        $res->getBody()->write('node 3');
        echo "3 after >>> \n";
        return $res;
    },
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
//        $res = HttpFactory::createResponse();
//        $res->getBody()->write('content');

        echo ">>> 4 before \n";
        $res = $handler->handle($request);
        $res->getBody()->write('node 4');
        echo "4 after >>> \n";

        return $res;
    }
], function (ServerRequestInterface $request) {
    echo " (this is core)\n";

    return HttpFactory::createResponse();
});

$request = HttpFactory::createServerRequest('GET', 'http://www.abc.com/home');

$res = $chain->process($request);
