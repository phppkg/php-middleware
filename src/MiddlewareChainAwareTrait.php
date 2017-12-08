<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-12-08
 * Time: 11:44
 */

namespace Inhere\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Trait MiddlewareChainAwareTrait
 * @package Inhere\Middleware
 *
 * ```php
 *
 * class MyApp implements RequestHandlerInterface {
 *  public function handleRequest(ServerRequestInterface $request): ResponseInterface
 *  {
 *      return new Response;
 *  }
 * }
 * ```
 */
trait MiddlewareChainAwareTrait
{
    /** @var array */
    private $stack;

    /** @var bool */
    private $locked = false;

    /** @var CallableResolverInterface */
    private $callableResolver;

    /**
     * @param callable[] ...$middleware
     * @return $this
     */
    public function use(...$middleware)
    {
        return $this->add(...$middleware);
    }

    /**
     * Add middleware
     * This method prepends new middleware to the application middleware stack.
     * @param array ...$middleware Any callable that accepts two arguments:
     *                           1. A Request object
     *                           2. A Handler object
     * @return $this
     */
    public function add(...$middleware)
    {
        if ($this->locked) {
            throw new \RuntimeException('Middleware can’t be added once the stack is dequeuing');
        }

        foreach ($middleware as $item) {
            $this->stack[] = $item;
        }

        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function run(ServerRequestInterface $request)
    {
        $this->locked = true;
        $response = $this->handle($request);
        $this->locked = false;

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var RequestHandlerInterface $handler */
        $handler = $this;
        // $handler = clone $this;

        // IMPORTANT: if no middleware. this is end point of the chain.
        if (null === key($handler->stack)) {
            // debug_print_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            return $this->handleRequest($request);
        }

        $middleware = current($handler->stack);
        next($handler->stack);

        if ($middleware instanceof MiddlewareInterface) {
            $response = $middleware->process($request, $handler);
        } elseif (\is_callable($middleware)) {
            $response = $middleware($request, $handler);
        } elseif ($this->callableResolver) {
            $middleware = $this->callableResolver->resolve($middleware);
            $response = $middleware($request, $handler);
        } else {
            throw new \InvalidArgumentException('The middleware is not a callable.');
        }

        if (!$response instanceof ResponseInterface) {
            throw new \UnexpectedValueException('Middleware must return instance of \Psr\Http\Message\ResponseInterface');
        }

        return $response;
    }

    /**
     * 在这里处理请求返回响应对象
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    abstract public function handleRequest(ServerRequestInterface $request): ResponseInterface;

    /**
     * @return bool
     */
    public function isLocked(): bool
    {
        return $this->locked;
    }

    /**
     * @param CallableResolverInterface $callableResolver
     */
    public function setCallableResolver(CallableResolverInterface $callableResolver)
    {
        $this->callableResolver = $callableResolver;
    }

    /**
     * @return CallableResolverInterface
     */
    public function getCallableResolver(): CallableResolverInterface
    {
        return $this->callableResolver;
    }
}