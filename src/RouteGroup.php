<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router;

use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterface;
use Spiral\Http\Pipeline;
use Spiral\Router\Target\Action;

/**
 * RouteGroup provides the ability to configure multiple routes to controller/actions using same presets.
 */
final class RouteGroup
{
    /** @var ContainerInterface */
    private $container;

    /** @var Pipeline */
    private $pipeline;

    /** @var CoreInterface */
    private $core;

    /**
     * @param ContainerInterface $container
     * @param Pipeline           $pipeline
     */
    public function __construct(ContainerInterface $container, Pipeline $pipeline)
    {
        $this->container = $container;
        $this->pipeline = $pipeline;
    }

    /**
     * @param CoreInterface|string|Autowire $core
     * @return $this
     */
    public function setCore($core): self
    {
        if (!$core instanceof CoreInterface) {
            $core = $this->container->get($core);
        }
        $this->core = $core;

        return $this;
    }

    /**
     * @param MiddlewareInterface|string $middleware
     * @return $this
     */
    public function addMiddleware($middleware): self
    {
        if (!$middleware instanceof MiddlewareInterface) {
            $middleware = $this->container->get($middleware);
        }

        $this->pipeline->pushMiddleware($middleware);

        return $this;
    }

    /**
     * @param string $pattern
     * @param string $controller
     * @param string $action
     * @return Route
     */
    public function createRoute(string $pattern, string $controller, string $action): Route
    {
        $action = new Action($controller, $action);
        if ($this->core !== null) {
            $action = $action->withCore($this->core);
        }

        $route = new Route($pattern, $action);

        // all routes within group share the same middleware pipeline
        $route = $route->withMiddleware($this->pipeline);

        return $route;
    }
}
