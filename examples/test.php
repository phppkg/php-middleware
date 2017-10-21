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
use Inhere\Middleware\MiddlewareAwareTrait;
use Inhere\Middleware\RequestHandler;
use Inhere\Middleware\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;

require dirname(__DIR__) . '/../../autoload.php';

$dispatcher = new class implements \Inhere\Middleware\MiddlewareInterface {
    use MiddlewareAwareTrait;

    public function run($request)
    {
        return $this->callMiddlewareStack($request);
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        return $handler->handle($request);
    }
};

function func_middleware($request, RequestHandlerInterface $handler)
{
    echo " >>> before 1\n";
    $res = $handler->handle($request);
    echo " after 1 >>>\n";

    return $res;
}

$dispatcher->add(
    'func_middleware',
    function ($request, RequestHandlerInterface $handler) {
        echo " >>> before 2\n";
        $res = $handler->handle($request);
        echo " after 2 >>>\n";

        return $res;
        // },
        // function (Request $request) {
        //     $res = new Response();
        //     $res->getBody()->write('end'); // abort middleware stack and return the response
        //     return $res;
    }
);
// var_dump($dispatcher);die;

$request = new ServerRequest('GET', Uri::createFromString('/home'));
//$response = $dispatcher->dispatch($request);
$response = $dispatcher->run($request);

// var_dump($response);
