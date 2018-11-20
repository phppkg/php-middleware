<?php
/**
 * Created by PhpStorm.
 * User: Inhere
 * Date: 2018/3/31 0031
 * Time: 00:35
 */

namespace Inhere\Middleware\Test;

use Inhere\Http\HttpFactory;
use Inhere\Middleware\MiddlewareStack;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class MiddlewareTest
 * @package Inhere\Middleware\Test
 */
class MiddlewareTest extends TestCase
{
    public function testMiddle()
    {
        $stack = new MiddlewareStack();

        // add more middleware
        require __DIR__ . '/some-example.php';

        $stack->setCoreHandler(function (ServerRequestInterface $request) {
            $leftVal = $request->getAttribute('test');

            $res = HttpFactory::createResponse();
            $res->getBody()->write($leftVal . '(CORE)');

            return $res;
        });

        $res = $stack(HttpFactory::createServerRequest('GET', '/test'));
        $body = (string)$res->getBody();

        // echo "\n\nThe Result:\n\n $body";

        $this->assertStringStartsWith('func0 >', $body);
        $this->assertStringEndsWith('> func0', $body);
        $this->assertTrue(\strpos($body, '(CORE)') > 0);
        $this->assertTrue(\strpos($body, 'closure0') > 0);
        $this->assertTrue(\strpos($body, 'class0') > 0);
    }
}
