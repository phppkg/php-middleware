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
class Dispatcher implements MiddlewareInterface
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

    public function run(ServerRequestInterface $request)
    {
        if (null === $this->stack) {
            $this->prepareStack();
        }

        /** @var call $start It is last added. */
        // $start = $this->stack->top();
        // var_dump($start);die;
        // $response = $start->process($request, $this->handler);
        $handler = $this->handler;
        $middleware = $this->stack->top();
        $middleware = $this->resolve($middleware);

        $this->locked = true;
        if (method_exists($middleware, '__invoke')) {
            $response = $middleware($request, $handler);
        } elseif ($middleware instanceof MiddlewareInterface) {
            $response = $middleware->process($request, $handler);
        } elseif (is_callable($middleware)) {
            $response = $middleware($request, $handler);
        }
        $this->locked = false;

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        var_dump($request, $handler);die;
        $response = $handler->handle($request);

        if (!$response instanceof ResponseInterface) {
            throw new \HttpInvalidParamException('error response');
        }

        // $response = $this->dispatch($request);

        return $response;
    }

    private function resolve1($middleware)
    {
        $middleware = $this->resolver ? ($this->resolver)($middleware) : $middleware; // as-is

        return $middleware;
    }

    /**
     * @param mixed $middleware stack index
     * @return callable
     */
    private function resolve()
    {
        if ($this->stack->isEmpty()) {
            return new RequestHandler(function () {
                throw new \LogicException("unresolved request: middleware stack exhausted with no result");
            });
        }

        return new RequestHandler(function (ServerRequestInterface $request) {
            $middleware = $this->stack->bottom();
            $middleware = $this->resolver ? ($this->resolver)($middleware) : $middleware; // as-is

            switch (true) {
                case $middleware instanceof MiddlewareInterface:
                    $result = $middleware->process($request, $this->resolve());
                    break;
                case is_callable($middleware):
                    $result = $middleware($request, $this->resolve());
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
