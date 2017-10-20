<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-10-17
 * Time: 11:24
 * @link      https://github.com/slimphp/Slim
 * @copyright Copyright (c) 2011-2017 Josh Lockhart
 * @license   https://github.com/slimphp/Slim/blob/3.x/LICENSE.md (MIT License)
 */

namespace Inhere\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;
use SplDoublyLinkedList;
use SplStack;
use UnexpectedValueException;

/**
 * Middleware
 * This is an internal class that enables concentric middleware layers. This
 * class is an implementation detail and is used only inside of the Slim
 * application; it is not visible to—and should not be used by—end users.
 */
trait MiddlewareAwareTrait
{
    /**
     * Middleware call stack
     * @var  \SplStack
     * @link http://php.net/manual/class.splstack.php
     */
    protected $stack;

    /**
     * Middleware stack lock
     * @var bool
     */
    protected $middlewareLock = false;

    /**
     * Add middleware
     * This method prepends new middleware to the application middleware stack.
     * @param callable $callable Any callable that accepts three arguments:
     *                           1. A Request object
     *                           2. A Response object
     *                           3. A "next" middleware callable
     * @return static
     * @throws RuntimeException         If middleware is added while the stack is dequeuing
     * @throws UnexpectedValueException If the middleware doesn't return a Psr\Http\Message\ResponseInterface
     */
    protected function addMiddleware(callable $callable)
    {
        if ($this->middlewareLock) {
            throw new RuntimeException('Middleware can’t be added once the stack is dequeuing');
        }

        if (null === $this->stack) {
            $this->seedMiddlewareStack();
        }

        $next = $this->stack->top();
        $this->stack[] = function (ServerRequestInterface $request, ResponseInterface $response) use ($callable, $next) {
            $result = $callable($request, $response, $next);
            if ($result instanceof ResponseInterface === false) {
                throw new UnexpectedValueException('Middleware must return instance of \Psr\Http\Message\ResponseInterface');
            }

            return $result;
        };

        return $this;
    }

    /**
     * Seed middleware stack with first callable(setting the core node of the middleware stack)
     * @param callable|mixed $kernel The last item to run as middleware
     * @throws RuntimeException if the stack is seeded more than once
     */
    protected function seedMiddlewareStack(callable $kernel = null)
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
     * Call middleware stack
     * @param  ServerRequestInterface $request A request object
     * @param  ResponseInterface $response A response object
     * @return ResponseInterface
     */
    public function callMiddlewareStack(ServerRequestInterface $request, ResponseInterface $response)
    {
        if (null === $this->stack) {
            $this->seedMiddlewareStack();
        }

        /** @var callable $start */
        $start = $this->stack->top();
        $this->middlewareLock = true;
        $response = $start($request, $response);
        $this->middlewareLock = false;

        return $response;
    }
}
