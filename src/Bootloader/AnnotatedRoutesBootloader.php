<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Bootloader;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Boot\EnvironmentInterface;
use Spiral\Boot\MemoryInterface;
use Spiral\Bootloader\ConsoleBootloader;
use Spiral\Bootloader\Http\RouterBootloader;
use Spiral\Core\Container\SingletonInterface;
use Spiral\Router\Command\ResetCommand;
use Spiral\Router\GroupRegistry;
use Spiral\Router\RouteLocator;
use Spiral\Router\RouterInterface;

/**
 * Configures application routes using annotations and pre-defined configuration groups.
 */
final class AnnotatedRoutesBootloader extends Bootloader implements SingletonInterface
{
    public const MEMORY_SECTION = 'routes';

    protected const DEPENDENCIES = [
        RouterBootloader::class,
        ConsoleBootloader::class
    ];

    protected const SINGLETONS = [
        GroupRegistry::class => [self::class, 'getGroupRegistry']
    ];

    /** @var MemoryInterface */
    private $memory;

    /** @var GroupRegistry */
    private $groupRegistry;

    /**
     * @param MemoryInterface $memory
     * @param GroupRegistry   $groupRegistry
     */
    public function __construct(MemoryInterface $memory, GroupRegistry $groupRegistry)
    {
        $this->memory = $memory;
        $this->groupRegistry = $groupRegistry;
    }

    /**
     * @param EnvironmentInterface $env
     * @param ConsoleBootloader    $console
     * @param RouterInterface      $router
     * @param RouteLocator         $locator
     */
    public function boot(
        ConsoleBootloader $console,
        EnvironmentInterface $env,
        RouterInterface $router,
        RouteLocator $locator
    ): void {
        $console->addCommand(ResetCommand::class);

        $cached = $env->get('ROUTE_CACHE', !$env->get('DEBUG'));
        AnnotationRegistry::registerLoader('class_exists');

        $schema = $this->memory->loadData(self::MEMORY_SECTION);
        if (empty($schema) || !$cached) {
            $schema = $locator->findDeclarations();
            $this->memory->saveData(self::MEMORY_SECTION, $schema);
        }

        $this->configureRoutes($router, $schema);
    }

    /**
     * @return GroupRegistry
     */
    public function getGroupRegistry(): GroupRegistry
    {
        return $this->groupRegistry;
    }

    /**
     * @param RouterInterface $router
     * @param array           $routes
     */
    private function configureRoutes(RouterInterface $router, array $routes): void
    {
        foreach ($routes as $name => $schema) {
            $route = $this->groupRegistry->getGroup($schema['group'])->createRoute(
                $schema['pattern'],
                $schema['controller'],
                $schema['action']
            );

            if ($schema['defaults'] !== []) {
                $route = $route->withDefaults($schema['defaults']);
            }

            $router->setRoute(
                $name,
                $route->withVerbs(...$schema['verbs'])->withMiddleware(...$schema['middleware'])
            );
        }
    }
}
