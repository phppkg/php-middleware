<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/16
 * Time: 下午11:27
 */

namespace Inhere\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CallableMiddleware
 * @package Inhere\Middleware
 */
class CallableMiddleware implements MiddlewareInterface
{
    /** @var callable  */
    protected $handler;

    /**
     * CallableMiddleware constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->handler = $callable;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
//        return ($this->handler)($request, $handler);
        return CallableHandler::execute($this->handler, [$request, $handler]);
    }
}
