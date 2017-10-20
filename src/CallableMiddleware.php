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
    protected $callable;

    /**
     * CallableMiddleware constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * {@inheritDoc}
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        return ($this->callable)($request, $handler);
    }
}
