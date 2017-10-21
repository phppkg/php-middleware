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
use RuntimeException;
use SplStack;
use SplDoublyLinkedList;

/**
 * Class Dispatcher
 * @link https://github.com/mindplay-dk/middleware
 * @package Inhere\Middleware
 */
class Dispatcher //implements MiddlewareInterface
{
    /**
     * the "resolver" is any callable with a signature like `function (string $name) : MiddlewareInterface`
     * @var callable middleware resolver
     */
    private $resolver;

    /**
     * @var \SplStack unresolved middleware stack
     */
    private $stack;

    /**
     * Middleware stack lock
     * @var bool
     */
    protected $locked = false;

    /**
     * @param callable|null $resolver
     */
    public function __construct(callable $resolver = null)
    {
        $this->resolver = $resolver;
    }

    /**
     * @param array ...$middleware accepts MiddlewareInterface, callable
     * @return $this
     */
    public function add(...$middleware)
    {
        if ($this->locked) {
            throw new RuntimeException('Middleware can’t be added once the stack is dequeuing');
        }

        if (null === $this->stack) {
            $this->prepareStack();
        }

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
     * @return ResponseInterface
     * @throws \LogicException on unexpected result from any middleware on the stack
     */
    public function dispatch(ServerRequestInterface $request)
    {
        // $resolved = $this->resolve(0);
        $resolved = $this->resolve();

        return $resolved->handle($request);
    }

    private function resolve1($middleware)
    {
        $middleware = $this->resolver ? ($this->resolver)($middleware) : $middleware; // as-is

        return $middleware;
    }

    /**
     * param int $index stack index
     * @return RequestHandlerInterface
     */
    private function resolve()
    {
        return new RequestHandler(function (ServerRequestInterface $request) {
            if ($this->stack->isEmpty()) {
                $middleware = new CallableMiddleware(function () {
                });
            } else {
                $middleware = $this->stack->bottom();
                $middleware = $this->resolver ? ($this->resolver)($middleware) : $middleware;
            }

//            if ($middleware instanceof Closure) {
//                $middleware = new CallableMiddleware($middleware);
//            }

            switch (true) {
                case $middleware instanceof MiddlewareInterface:
                    $response = $middleware->process($request, $this->resolve());
                    break;
                case is_callable($middleware):
                    $response = $middleware($request, $this->resolve());
                    break;
                default:
                    $given = gettype($middleware);
                    throw new \LogicException("unsupported middleware type: {$given}");
            }

            if (!($response instanceof ResponseInterface)) {
                throw new \UnexpectedValueException(
                    sprintf('The middleware must return an instance of %s', ResponseInterface::class)
                );
            }

            return $response;
        });
    }

    /**
     * Seed middleware stack with first callable(setting the core node of the middleware stack)
     * @param callable|mixed $kernel The last item to run as middleware
     * @throws RuntimeException if the stack is prepared more than once
     */
    protected function prepareStack($kernel = null)
    {
        if (null !== $this->stack) {
            throw new RuntimeException('Middleware stack can only be prepared once.');
        }

        // setting the core node use self.
        if ($kernel === null) {
            $kernel = $this;
        }

        $this->stack = new SplStack;
        $this->stack->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP);
        // $this->stack[] = $kernel;
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

    public function setHandler(RequestHandlerInterface $handler)
    {
        $this->handler = $handler;
    }

}
