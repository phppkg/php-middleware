<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-10-20
 * Time: 10:43
 */

namespace Inhere\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MiddlewareChain
 * @package Inhere\Middleware
 */
class MiddlewareChain implements RequestHandlerInterface
{
    /**
     * Call chaining.
     *
     * @param ServerRequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    public function run($request, $response)
    {
        // Start call chaining.
        return $this->handle($request, $response);
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Implement handle() method.
    }
}