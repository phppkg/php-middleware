<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-10-17
 * Time: 10:43
 */

use Inhere\Http\Request;
use Inhere\Http\Response;
use Inhere\Http\Uri;
use Inhere\Middleware\RequestHandler;
use Inhere\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

require dirname(__DIR__) . '/../../vendor/autoload.php';

$handler = new RequestHandler(
    new Response(),
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        echo "1 before >>> \n";
        $res = $handler->handle($request);
        $res->getBody()->write('+node 1');
        echo "1 after <<< \n";

        return $res;
    },
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        echo "2 before >>> \n";
        $res = $handler->handle($request);
        $res->getBody()->write('+node 2');
        echo "2 after <<< \n";

        return $res;
    },
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        echo "3 before >>> \n";
        $res = $handler->handle($request);
        $res->getBody()->write('+node 3');
        echo "3 after <<< \n";

        return $res;
    },
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        $res = $handler->getResponse();
        $res->getBody()->write('content');

        echo  "<<handle route and dispatch on there>>.\n";

        return $res;
    }
);

//$handler->setResponse(new Response());

$res = $handler->handle(new Request('GET', Uri::createFromString('/home')));

echo PHP_EOL . 'response content: ', (string)$res->getBody() . PHP_EOL;

/*
OUTPUT:

$ php examples/use_req_handler.php
1 before >>>
2 before >>>
3 before >>>
3 after <<<
2 after <<<
1 after <<<
response: content++node 3+node 2+node 1

 */