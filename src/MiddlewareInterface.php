<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-10-16
 * Time: 16:54
 */

namespace Inhere\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface MiddlewareInterface
 * @package Inhere\Middleware
 * @link https://github.com/php-fig/fig-standards/blob/master/proposed/http-middleware/middleware.md
 *
 * An HTTP middleware component participates in processing an HTTP message,
 * either by acting on the request or the response. This interface defines the
 * methods required to use the middleware.
 */
interface MiddlewareInterface
{
    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler);
}
