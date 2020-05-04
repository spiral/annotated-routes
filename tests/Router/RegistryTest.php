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
use Spiral\Core\Container;
use Spiral\Router\GroupRegistry;

class RegistryTest extends TestCase
{
    public function testSameGroup(): void
    {
        $registry = new GroupRegistry(new Container());

        $group = $registry->getGroup('default');
        $this->assertSame($group, $registry->getGroup('default'));
    }
}
