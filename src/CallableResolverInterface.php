<?php

namespace Inhere\Middleware;

/**
 * Resolves a callable.
 *
 * @package Inhere\Middleware
 * @from slim 3
 */
interface CallableResolverInterface
{
    /**
     * Invoke the resolved callable.
     *
     * @param mixed $toResolve
     *
     * @return callable
     */
    public function resolve($toResolve);
}
