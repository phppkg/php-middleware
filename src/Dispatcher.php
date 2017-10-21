<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/16
 * Time: 下午11:09
 */

namespace Inhere\Middleware;

use Inhere\Http\HttpFactory;
use Inhere\Library\Web\Environment;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Closure;
use UnexpectedValueException;

class Dispatcher
{
    /**
     * @var MiddlewareInterface[]
     */
    private $stack;

    /**
     * Static helper to create and dispatch a request.
     *
     * @param MiddlewareInterface[]
     * @param ServerRequestInterface|null $request
     *
     * @return ResponseInterface
     */
    public static function run(array $stack = [], ServerRequestInterface $request = null)
    {
        if ($request === null) {
            $request = HttpFactory::createServerRequestFromArray(Environment::mock());
        }

        return (new static($stack))->dispatch($request);
    }

    /**
     * @param MiddlewareInterface[] $stack middleware stack (with at least one middleware component)
     */
    public function __construct(array $stack = [])
    {
        $this->stack = $stack;
    }

    /**
     * @param array ...$middleware accepts MiddlewareInterface, callable
     * @return $this
     */
    public function add(...$middleware)
    {
        // $this->stack[] = function(ServerRequestInterface $request, RequestHandlerInterface $handler) use ($middleware) {
        //     return $middleware->process($request, $handler);
        // };

        foreach ($middleware as $item) {
            $this->stack[] = $item;
        }

        return $this;
    }

    /**
     * Dispatches the middleware stack and returns the resulting `ResponseInterface`.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function dispatch(ServerRequestInterface $request)
    {
        $resolved = $this->resolve(0);
        return $resolved->handle($request);
    }

    /**
     * @param int $index middleware stack index
     *
     * @return RequestHandlerInterface
     */
    private function resolve($index)
    {
        return new RequestHandler(function (ServerRequestInterface $request) use ($index) {
            $middleware = $this->stack[$index] ?? new CallableMiddleware(function () {
            });

            if (is_string($middleware) || $middleware instanceof Closure) {
                $middleware = new CallableMiddleware($middleware);
            }

            if (!($middleware instanceof MiddlewareInterface)) {
                throw new UnexpectedValueException(
                    sprintf('The middleware must be an instance of %s', MiddlewareInterface::class)
                );
            }

            $response = $middleware->process($request, $this->resolve($index + 1));

            if (!($response instanceof ResponseInterface)) {
                throw new UnexpectedValueException(
                    sprintf('The middleware must return an instance of %s', ResponseInterface::class)
                );
            }

            return $response;
        });
    }
}
