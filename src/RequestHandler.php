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
use SplDoublyLinkedList;
use SplStack;

/**
 * Class RequestHandler
 * @package Inhere\Middleware
 */
class RequestHandler implements RequestHandlerInterface
{
    /** @var ResponseInterface */
    protected $response;

    protected $responseFactory;

    /** @var MiddlewareInterface[] */
    protected $middlewares;

    private $stack;

    /** @var bool  */
    private $locked = false;

    /**
     * RequestHandler constructor.
     * @param ResponseInterface $response
     * @param MiddlewareInterface[] ...$middlewares
     */
    public function __construct(ResponseInterface $response,...$middlewares)
    {
        $this->response = $response;
        $this->middlewares = $middlewares;
        $this->stack = new \SplStack();
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

        if (null === $this->stack) {
            $this->seedStack();
        }

        foreach ($middleware as $item) {
            $this->stack[] = $item;
        }

        return $this;
    }

    /**
     * Seed middleware stack with first callable(setting the core node of the middleware stack)
     * @param callable|mixed $kernel The last item to run as middleware
     * @throws RuntimeException if the stack is seeded more than once
     */
    protected function seedStack(callable $kernel = null)
    {
        if (null !== $this->stack) {
            throw new RuntimeException('MiddlewareStack can only be seeded once.');
        }

        // setting the core node use self.
        if ($kernel === null) {
            $kernel = $this;
        }

        $this->stack = new SplStack;
        $this->stack->setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO | SplDoublyLinkedList::IT_MODE_KEEP);
        $this->stack[] = $kernel;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $handler = $this;
//        $handler = clone $this;

        if (null === key($handler->middlewares)) {
//            return $this->responseFactory->createResponse();
            return $this->response;
        }

        printf("%s line %d\n", __METHOD__,__LINE__);

        $response = $this->response;
        $middleware = current($handler->middlewares);
        next($handler->middlewares);

        if (method_exists($middleware, '__invoke')) {
            $response = $middleware($request, $handler);
        } elseif ($middleware instanceof MiddlewareInterface) {
            $response = $middleware->process($request, $handler);
        }

        printf("%s line %d\n", __METHOD__,__LINE__);

        if (!$response instanceof ResponseInterface) {
            throw new \HttpInvalidParamException('error response');
        }

        return ($this->response = $response);
    }

    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param ResponseInterface $response
     */
    public function setResponse(ResponseInterface $response)
    {
        $this->response = $response;
    }

}
