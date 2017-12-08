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

/**
 * Class MiddlewareChain
 * @package Inhere\Middleware
 */
class MiddlewareStack implements RequestHandlerInterface
{
    use MiddlewareStackAwareTrait;

    /** @var callable */
    private $coreHandler;

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
     */
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->coreHandler)($request);
    }
}
