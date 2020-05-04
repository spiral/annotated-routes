<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Router\Tests;

use PHPUnit\Framework\TestCase;
use Spiral\App\App;
use Spiral\Boot\Environment;
use Spiral\Http\Http;

class CacheTest extends TestCase
{
    private $app;

    public function setUp(): void
    {
        parent::setUp();
        $this->app = $this->makeApp(['DEBUG' => false]);
    }

    public function testCache(): void
    {
        $this->assertFileExists(__DIR__ . '/../runtime/cache/routes.php');
        $this->assertCount(2, include __DIR__ . '/../runtime/cache/routes.php');

        $this->app->getConsole()->run('route:reset');

        $this->assertSame(null, include __DIR__ . '/../runtime/cache/routes.php');
    }

    /**
     * @param array $env
     * @return Http
     * @throws \Throwable
     */
    private function makeApp(array $env): App
    {
        return App::init([
            'root' => dirname(__DIR__)
        ], new Environment([
            'DEBUG' => true
        ]), false);
    }
}
