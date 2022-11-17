<?php

declare(strict_types=1);

namespace Spiral\Tests\Router;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\UriFactoryInterface;
use Spiral\Attributes\Factory;
use Spiral\Core\Container;
use Spiral\Router\GroupRegistry;
use Spiral\Router\RouteLocatorListener;
use Spiral\Router\Router;
use Spiral\Router\RouterInterface;
use Spiral\Router\UriHandler;
use Spiral\Tests\Router\App\Controller\PageController;

final class RouteLocatorListenerTest extends TestCase
{
    private RouteLocatorListener $listener;
    private Container $container;

    protected function setUp(): void
    {
        $this->configureRouter();
    }

    public function testDefaultGroup(): void
    {
        $this->listener->listen(new \ReflectionClass(PageController::class));
        $this->listener->finalize();

        $groups = $this->container->get(GroupRegistry::class);

        $this->assertSame(['web'], \array_keys(\iterator_to_array($groups)));
    }

    public function testChangedDefaultGroup(): void
    {
        $groups = $this->container->get(GroupRegistry::class);
        $groups->setDefaultGroup('other');

        $this->listener->listen(new \ReflectionClass(PageController::class));
        $this->listener->finalize();

        $groups = $this->container->get(GroupRegistry::class);

        $this->assertSame(['other'], \array_keys(\iterator_to_array($groups)));
    }

    private function configureRouter(): void
    {
        $this->container = new Container();

        $this->container->bindSingleton(UriFactoryInterface::class, new Psr17Factory());
        $this->container->bindSingleton(RouterInterface::class, static function (UriHandler $handler, Container $container) {
            return new Router('/', $handler, $container);
        });
        $this->container->bindSingleton(GroupRegistry::class, new GroupRegistry($this->container));

        $this->listener = new RouteLocatorListener(
            (new Factory())->create(),
            $this->container->get(GroupRegistry::class)
        );
    }
}
