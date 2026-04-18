<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Resolver\Lazy;

use Phalcon\Bucket\Resolver\Lazy\FunctionCall;
use Phalcon\Bucket\Resolver\Lazy\Get;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class GetTest extends AbstractUnitTestCase
{
    private function makeContainer(): object
    {
        return new class () {
            public string $lastId = '';

            public function get(string $id): mixed
            {
                $this->lastId = $id;
                return new stdClass();
            }

            public function new(string $id): mixed
            {
                return new stdClass();
            }
        };
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyGetInvokeDelegatesToResolve(): void
    {
        $container = $this->makeContainer();
        $lazy      = new Get('SomeService');
        $result    = $lazy($container);
        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyGetResolvePassesCorrectIdToContainer(): void
    {
        $container = $this->makeContainer();
        $lazy      = new Get('MyService');
        $lazy->resolve($container);
        $this->assertSame('MyService', $container->lastId);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyGetResolveReturnsServiceFromContainer(): void
    {
        $container = $this->makeContainer();
        $lazy      = new Get('SomeService');
        $result    = $lazy->resolve($container);
        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyGetResolveWithNestedLazyId(): void
    {
        $obj       = new \stdClass();
        $container = new class ($obj) {
            public string $lastId = '';
            public function __construct(private \stdClass $obj)
            {
            }
            public function get(string $id): mixed
            {
                $this->lastId = $id;
                return $this->obj;
            }
            public function new(string $id): mixed
            {
                return new \stdClass();
            }
        };

        // Inner lazy resolves to the service name string
        $inner = new FunctionCall('strtolower', ['TARGET']);
        $outer = new Get($inner);
        $result = $outer->resolve($container);

        $this->assertSame($obj, $result);
        $this->assertSame('target', $container->lastId);
    }
}
