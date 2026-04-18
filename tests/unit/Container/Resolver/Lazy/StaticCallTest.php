<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Container\Resolver\Lazy;

use Phalcon\Container\Resolver\Lazy\StaticCall;
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
    public function testContainerResolverLazyStaticCallInvokeDelegatesToResolve(): void
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
    public function testContainerResolverLazyStaticCallResolveCallsStaticMethod(): void
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
    public function testContainerResolverLazyStaticCallResolveWithNoArguments(): void
    {
        $container = $this->makeContainer();
        $lazy      = new StaticCall(\DateTime::class, 'createFromFormat', ['U', '0']);
        $result    = $lazy->resolve($container);
        $this->assertInstanceOf(\DateTime::class, $result);
    }
}
