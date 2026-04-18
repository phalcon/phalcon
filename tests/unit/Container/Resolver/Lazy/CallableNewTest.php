<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Container\Resolver\Lazy;

use Phalcon\Container\Resolver\Lazy\CallableNew;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class CallableNewTest extends AbstractUnitTestCase
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
    public function testContainerResolverLazyCallableNewClosureInvokesContainerNew(): void
    {
        $container = $this->makeContainer();
        $lazy      = new CallableNew('SomeService');
        $closure   = $lazy->resolve($container);
        $result    = $closure();
        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testContainerResolverLazyCallableNewInvokeReturnsClosure(): void
    {
        $container = $this->makeContainer();
        $lazy      = new CallableNew('SomeService');
        $result    = $lazy($container);
        $this->assertIsCallable($result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testContainerResolverLazyCallableNewResolveReturnsClosure(): void
    {
        $container = $this->makeContainer();
        $lazy      = new CallableNew('SomeService');
        $result    = $lazy->resolve($container);
        $this->assertIsCallable($result);
    }
}
