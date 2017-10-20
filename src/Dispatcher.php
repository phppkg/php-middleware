<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/16
 * Time: 下午11:09
 */

namespace Inhere\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Dispatcher
 * @package Inhere\Middleware
 */
class Dispatcher implements MiddlewareInterface
{
    /**
     * @var callable middleware resolver
     */
    private $resolver;

    /**
     * @var mixed[] unresolved middleware stack
     */
    private $stack;

    /**
     * @param array $stack middleware stack (with at least one middleware component)
     *
     * @throws \InvalidArgumentException if an empty middleware stack was given
     */
    public function __construct(...$stack)
    {
        if (count($stack) === 0) {
            throw new \InvalidArgumentException('an empty middleware stack was given');
        }
//        $this->storage = new \SplObjectStorage();
        $this->stack = $stack;
    }

    /**
     * @param MiddlewareInterface $middleware
     * @return $this
     */
    public function add(MiddlewareInterface $middleware)
    {
        $this->stack[] = function(ServerRequestInterface $request, RequestHandlerInterface $handler) use ($middleware) {
            return $middleware->process($request, $handler);
        };

        return $this;
    }

    /**
     * Dispatches the middleware stack and returns the resulting `ResponseInterface`.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \LogicException on unexpected result from any middleware on the stack
     */
    public function dispatch(ServerRequestInterface $request)
    {
        $resolved = $this->resolve(0);

        return $resolved($request);
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $this->stack[] = function (ServerRequestInterface $request) use ($handler) {
            return $handler->handle($request);
        };

        $response = $this->dispatch($request);
        array_pop($this->stack);

        return $response;
    }

    /**
     * @param int $index middleware stack index
     *
     * @return mixed
     */
    private function resolve($index)
    {
        if (!isset($this->stack[$index])) {
            return function () {
                throw new \LogicException('unresolved request: middleware stack exhausted with no result');
            };
        }

        return function (ServerRequestInterface $request) use ($index) {
            $middleware = $this->resolver
                ? ($this->resolver)($this->stack[$index])
                : $this->stack[$index]; // as-is

            switch (true) {
                case $middleware instanceof MiddlewareInterface:
                    $result = $middleware->process($request, $this->resolve($index + 1));
                    break;
                case is_callable($middleware):
                    $result = $middleware($request, $this->resolve($index + 1));
                    break;
                default:
                    $given = gettype($middleware);
                    throw new \LogicException("unsupported middleware type: {$given}");
            }

            if (!$result instanceof ResponseInterface) {
                $given = json_encode($result);
                $source = is_object($middleware) ? get_class($middleware) : $middleware;
                throw new \LogicException("unexpected middleware result: {$given} returned by: {$source}");
            }

            return $result;
        };
    }

    /**
     * @param callable $resolver optional middleware resolver:
     *
     * ```php
     * function (string $name): MiddlewareInterface
     * ```
     */
    public function setResolver(callable $resolver)
    {
        $this->resolver = $resolver;
    }

    public function run(ServerRequestInterface $request) : ResponseInterface
    {
        $handler = clone $this;
//        reset($this->stack);

        if (null === key($handler->stack)) {
//            return $this->responseFactory->createResponse();
            return null;
        }

        $middleware = current($handler->stack);

        next($handler->stack);

        $response = $middleware->process($request, $handler);

        if (!$response instanceof ResponseInterface) {
            throw new \HttpResponseException('error response data');
        }

        return $response;
    }
}
