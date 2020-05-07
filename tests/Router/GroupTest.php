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
use Spiral\Http\Pipeline;
use Spiral\Router\RouteGroup;
use Spiral\Router\Target\AbstractTarget;
use Spiral\Router\Target\Action;

class GroupTest extends TestCase
{
    public function testCoreString(): void
    {
        $group = new RouteGroup(new Container(), new Pipeline(new Container()));

        $group->setCore(TestCore::class);

        $r = $group->createRoute('/', 'controller', 'method');
        $t = $this->getProperty($r, 'target');

        $this->assertInstanceOf(Action::class, $t);

        $this->assertSame('controller', $this->getProperty($t, 'controller'));
        $this->assertSame('method', $this->getProperty($t, 'action'));

        $this->assertSame($group, $this->getActionProperty($t, 'core'));
    }

    public function testCoreObject(): void
    {
        $group = new RouteGroup(new Container(), new Pipeline(new Container()));

        $group->setCore(new TestCore(new Container()));

        $r = $group->createRoute('/', 'controller', 'method');
        $t = $this->getProperty($r, 'target');

        $this->assertInstanceOf(Action::class, $t);

        $this->assertSame('controller', $this->getProperty($t, 'controller'));
        $this->assertSame('method', $this->getProperty($t, 'action'));

        $this->assertSame($group, $this->getActionProperty($t, 'core'));
    }

    public function testMiddleware(): void
    {
        $group = new RouteGroup(new Container(), new Pipeline(new Container()));
        $group->addMiddleware(TestMiddleware::class);

        $r = $group->createRoute('/', 'controller', 'method');

        $rl = new \ReflectionObject($r);
        $m = $rl->getMethod('makePipeline');
        $m->setAccessible(true);

        $p = $m->invoke($r);
        $m = $this->getProperty($p, 'middleware');

        $this->assertCount(1, $m);
        $this->assertInstanceOf(TestMiddleware::class, $m[0]);
    }

    /**
     * @param object $object
     * @param string $property
     * @return mixed
     * @throws \ReflectionException
     */
    private function getProperty(object $object, string $property)
    {
        $r = new \ReflectionObject($object);
        $p = $r->getProperty($property);
        $p->setAccessible(true);

        return $p->getValue($object);
    }

    /**
     * @param object $object
     * @param string $property
     * @return mixed
     * @throws \ReflectionException
     */
    private function getActionProperty(object $object, string $property)
    {
        $r = new \ReflectionClass(AbstractTarget::class);
        $p = $r->getProperty($property);
        $p->setAccessible(true);

        return $p->getValue($object);
    }
}
