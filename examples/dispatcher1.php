<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/20
 * Time: 下午8:27
 */

use Inhere\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Inhere\Middleware\Dispatcher;
use Inhere\Middleware\CallableMiddleware;

require dirname(__DIR__) . '/../../autoload.php';

$response = Dispatcher::run([
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        echo '01';
        $response = $handler->handle($request);
        $response->getBody()->write('3');

        return $response;
    },
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        echo '02';
        $response = $handler->handle($request);
        $response->getBody()->write('2-');

        return $response;
    },
    new CallableMiddleware(function ($request, RequestHandlerInterface $handler) {
        echo '(00)';

        return $handler->handle($request);
    }),
]);

var_dump((string)$response->getBody());
