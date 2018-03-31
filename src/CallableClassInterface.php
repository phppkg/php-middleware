<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/3/31 0031
 * Time: 13:51
 */

namespace Inhere\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class CallableClassInterface
 * @package Inhere\Middleware
 */
interface CallableClassInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface;
}
