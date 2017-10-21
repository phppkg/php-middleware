<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/20
 * Time: 下午9:23
 */

namespace Inhere\Middleware;

use Inhere\Http\HttpFactory;
use Psr\Http\Message\ResponseInterface;
use UnexpectedValueException;

/**
 * Simple class to execute callables and returns responses.
 */
abstract class CallableHandler
{
    /**
     * Execute the callable.
     *
     * @param callable $callable
     * @param array $arguments
     *
     * @return ResponseInterface
     * @throws \Throwable
     */
    public static function execute($callable, array $arguments = [])
    {
        ob_start();
        $level = ob_get_level();

        try {
            $return = $callable(...$arguments);

            if ($return instanceof ResponseInterface) {
                $response = $return;
                $return = '';
            } elseif (
                null === $return ||
                is_scalar($return) ||
                (is_object($return) && method_exists($return, '__toString'))
            ) {
                $response = HttpFactory::createResponse();
            } else {
                throw new UnexpectedValueException(
                    'The value returned must be scalar or an object with __toString method'
                );
            }

            while (ob_get_level() >= $level) {
                $return = ob_get_clean() . $return;
            }

            $body = $response->getBody();

            if ($return !== '' && $body->isWritable()) {
                $body->write($return);
            }

            return $response;
        } catch (\Throwable $exception) {
            while (ob_get_level() >= $level) {
                ob_end_clean();
            }

            throw $exception;
        }
    }
}
