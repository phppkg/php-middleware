<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/16
 * Time: 下午11:51
 */
use Inhere\Http\ServerRequest;
use Inhere\Http\Response;
use Inhere\Http\Uri;
use Inhere\Middleware\RequestHandler;
use Inhere\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

require dirname(__DIR__) . '/../../autoload.php';

$dispatcher = new \Inhere\Middleware\Dispatcher();
// $dispatcher->setHandler(new RequestHandler);

function func_middleware($request, RequestHandlerInterface $handler)
{
    echo " >>> before 1\n";
    $res = $handler->handle($request);
    echo " after 1 >>>\n";

    return $res;
}

$dispatcher->add(
    'func_middleware',
    function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
        echo " >>> before 2\n";
        $res = $handler->handle($request);
        echo " after 2 >>>\n";

        return $res;
     },
     function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
//         $res = new Response();
         $res = $handler->handle($request);
         $res->getBody()->write("\nend\n"); // abort middleware stack and return the response
         return $res;
    }
);
// var_dump($dispatcher);die;

$request = new ServerRequest('GET', Uri::createFromString('/home'));
$response = $dispatcher->dispatch($request);
// $response = $dispatcher->run($request);

 var_dump((string)$response->getBody());
