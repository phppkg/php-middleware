<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017-10-16
 * Time: 16:52
 */

namespace Inhere\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface RequestHandlerInterface
 * @package Inhere\Middleware
 * @link https://github.com/php-fig/fig-standards/blob/master/proposed/http-middleware/middleware.md
 * An HTTP request handler process a HTTP request and produces an HTTP response.
 * This interface defines the methods require to use the request handler.
 */
interface RequestHandlerInterface
{
    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface;
}
