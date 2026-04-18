<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Resolver\Lazy;

use Phalcon\Bucket\Resolver\Lazy\NewInstance;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class NewInstanceTest extends AbstractUnitTestCase
{
    private function makeContainer(): object
    {
        return new class () {
            public string $lastId = '';

            public function get(string $id): mixed
            {
                return new stdClass();
            }

            public function new(string $id): mixed
            {
                $this->lastId = $id;
                return new stdClass();
            }
        };
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyNewInstanceInvokeDelegatesToResolve(): void
    {
        $container = $this->makeContainer();
        $lazy      = new NewInstance('SomeService');
        $result    = $lazy($container);
        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyNewInstanceResolveReturnsNewInstanceFromContainer(): void
    {
        $container = $this->makeContainer();
        $lazy      = new NewInstance('SomeService');
        $result    = $lazy->resolve($container);
        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyNewInstanceResolveUsesCorrectId(): void
    {
        $container = $this->makeContainer();
        $lazy      = new NewInstance('MyService');
        $lazy->resolve($container);
        $this->assertSame('MyService', $container->lastId);
    }
}
