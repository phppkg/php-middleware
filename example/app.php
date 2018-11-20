<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-08
 * Time: 15:30
 */

use Inhere\Http\HttpFactory;
use Inhere\Http\HttpUtil;
use Inhere\Middleware\MiddlewareStackAwareTrait;
use Inhere\Route\RouterInterface;
use Inhere\Route\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

require dirname(__DIR__) . '/../../autoload.php';

$app = new class implements RequestHandlerInterface
{
    use MiddlewareStackAwareTrait;

    /**
     * @var Router
     */
    private $router;

    public function run(ServerRequestInterface $request)
    {
        $response = $this->callStack($request);

        HttpUtil::respond($response);
    }

    /**
     * 在这里处理请求返回响应对象
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws Throwable
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $uriPath = $request->getUri()->getPath();
        $response = HttpFactory::createResponse();

        try {
            // $this->router->match($uriPath, $method);
            $result = $this->router->dispatch(null, $uriPath, $method);
            $response->getBody()->write($result);
        } catch (Throwable $e) {
            $response->getBody()->write($e->getTraceAsString());
        }

        return $response;
    }

    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }
};

$router = new ORouter();

/**
 * add routes
 */
$router->get('/', function () {
    echo '(hello, world)';
});

$router->get('/hello/{name}', function ($args) {
    echo "hello, {$args['name']}";
});

/**
 * add middleware
 */
$app->use(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
    echo 'before handle0 > ';
    $res = $handler->handle($request);
    echo ' > after handle0';

    return $res;
});

$app->use(function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
    echo 'before handle1 > ';
    $res = $handler->handle($request);
    echo ' > after handle1';

    return $res;
});

/**
 * run
 */
$req = HttpFactory::createServerRequestFromArray($_SERVER);
// var_dump($_SERVER, $req);die;

$app->setRouter($router);
$app->run($req);
