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
use Inhere\Http\Response;

/**
 * Class RequestHandler
 * @package Inhere\Middleware
 * @ref https://github.com/mindplay-dk/middleman/blob/master/src/Delegate.php
 */
class RequestHandler implements RequestHandlerInterface
{
    /**
     * @var callable
     */
    protected $callback;

    /**
     * @param callable $callback function (RequestInterface $request) : ResponseInterface
     */
    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->callback)($request);
    }

    /**
     * Dispatch the next available middleware and return the response.
     *
     * This method duplicates `next()` to provide backwards compatibility with non-PSR 15 middleware.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request)
    {
        return $this->handle($request);
    }
}
