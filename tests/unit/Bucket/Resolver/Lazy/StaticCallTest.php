<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Resolver\Lazy;

use Phalcon\Bucket\Resolver\Lazy\StaticCall;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class StaticCallTest extends AbstractUnitTestCase
{
    private function makeContainer(): object
    {
        return new class () {
            public function get(string $id): mixed
            {
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
    public function testBucketResolverLazyStaticCallInvokeDelegatesToResolve(): void
    {
        $container = $this->makeContainer();
        $lazy      = new StaticCall('DateTime', 'createFromFormat', ['Y-m-d', '2024-06-01']);
        $result    = $lazy($container);
        $this->assertInstanceOf(\DateTime::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyStaticCallResolveCallsStaticMethod(): void
    {
        $container = $this->makeContainer();
        $lazy      = new StaticCall('DateTime', 'createFromFormat', ['Y-m-d', '2024-01-15']);
        $result    = $lazy->resolve($container);
        $this->assertInstanceOf(\DateTime::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyStaticCallResolveWithNoArguments(): void
    {
        $container = $this->makeContainer();
        $lazy      = new StaticCall(\DateTime::class, 'createFromFormat', ['U', '0']);
        $result    = $lazy->resolve($container);
        $this->assertInstanceOf(\DateTime::class, $result);
    }
}
