<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/10/20
 * Time: 下午9:53
 */

namespace Inhere\Middleware;


//use Interop\Http\ServerMiddleware\DelegateInterface;
use Inhere\Http\HttpFactory;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class Delegate
 * @package Inhere\Middleware
 * @from https://github.com/moon-php/http-middleware/blob/master/src/Delegate.php
 */
class Delegate implements RequestHandlerInterface
{
    /**
     * @var string[]|MiddlewareInterface[]|mixed $middlewares
     */
    protected $middlewares;

    /**
     * @var callable
     */
    private $default;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Delegate constructor.
     *
     * @param array $middlewares
     * @param callable $default
     * @param ContainerInterface|null $container
     */
    public function __construct(array $middlewares, callable $default, ContainerInterface $container = null)
    {
        $this->middlewares = $middlewares;
        $this->default = $default;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function process(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = array_shift($this->middlewares);

        // It there's no middleware use the default callable
        if ($middleware === null) {
            return ($this->default)($request);
            // return $this->handle($request);
        }

        if ($middleware instanceof MiddlewareInterface) {
            return $middleware->process($request, clone $this);
        }

        if (\is_callable($middleware)) {
            return $middleware($request, $this);
        }

        if (!$this->container instanceof ContainerInterface || !$this->container->has($middleware)) {
            throw new InvalidArgumentException(
                sprintf('The middleware is not a valid %s and is not passed in the Container', MiddlewareInterface::class),
                $middleware
            );
        }

//        array_unshift($this->middlewares, $this->container->get($middleware));
//        return $this->process($request);
        $middleware = $this->container->get($middleware);

        return $middleware($request);
    }

    /**
     * Handle the request and return a response.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // return ($this->default)($request);
        return $this->process($request);
//        return HttpFactory::createResponse();
    }
}
