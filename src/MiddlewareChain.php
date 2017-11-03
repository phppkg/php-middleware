<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/16
 * Time: 下午11:07
 */

namespace Inhere\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * Class MiddlewareChain
 * @package Inhere\Middleware
 */
class MiddlewareChain implements RequestHandlerInterface
{
    /** @var array */
    private $stack;

    /** @var bool */
    private $locked = false;

    /** @var callable */
    private $coreHandler;

    /** @var CallableResolverInterface */
    private $callableResolver;

    /**
     * RequestHandler constructor.
     * @param MiddlewareInterface[] $stack
     * @param CallableResolverInterface|null $callableResolver
     */
    public function __construct(array $stack = [], CallableResolverInterface $callableResolver = null)
    {
        $this->add(...$stack);
        $this->callableResolver = $callableResolver;
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
            throw new RuntimeException('Middleware can’t be added once the stack is dequeuing');
        }

        foreach ($middleware as $item) {
            $this->stack[] = $item;
        }

        return $this;
    }

    /**
     * @return callable
     */
    public function getCoreHandler()
    {
        return $this->coreHandler;
    }

    /**
     * @param callable $coreHandler
     */
    public function setCoreHandler($coreHandler)
    {
        $this->coreHandler = $coreHandler;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \InvalidArgumentException
     */
    public function run(ServerRequestInterface $request)
    {
        $this->locked = true;

        // append the core middleware
//        $this->stack[] = $this->coreHandler ?: $this;
        $response = $this->handle($request);

        $this->locked = false;

        return $response;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this;
        // $handler = clone $this;

        // IMPORTANT: if no middleware. this is end point of the chain.
        if (null === key($handler->stack)) {
            return ($this->coreHandler)($request);
        }

        $middleware = current($handler->stack);
        next($handler->stack);

        if ($middleware instanceof MiddlewareInterface) {
            $response = $middleware->process($request, $handler);
        } elseif (method_exists($middleware, '__invoke')) {
            $response = $middleware($request, $handler);
        } elseif (is_callable($middleware)) {
            $response = $middleware($request, $handler);
        } elseif ($this->callableResolver) {
            $middleware = $this->callableResolver->resolve($middleware);
            $response = $middleware($request, $handler);
        } else {
            throw new \InvalidArgumentException('The middleware is not a callable.');
        }

        if (!$response instanceof ResponseInterface) {
            throw new \UnexpectedValueException('error response');
        }

        return $response;
    }

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
}
