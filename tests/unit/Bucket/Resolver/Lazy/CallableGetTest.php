<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Resolver\Lazy;

use Phalcon\Bucket\Resolver\Lazy\CallableGet;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class CallableGetTest extends AbstractUnitTestCase
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
    public function testBucketResolverLazyCallableGetClosureInvokesContainerGet(): void
    {
        $container = $this->makeContainer();
        $lazy      = new CallableGet('SomeService');
        $closure   = $lazy->resolve($container);
        $result    = $closure();
        $this->assertInstanceOf(stdClass::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyCallableGetInvokeReturnsClosure(): void
    {
        $container = $this->makeContainer();
        $lazy      = new CallableGet('SomeService');
        $result    = $lazy($container);
        $this->assertIsCallable($result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyCallableGetResolveReturnsClosure(): void
    {
        $container = $this->makeContainer();
        $lazy      = new CallableGet('SomeService');
        $result    = $lazy->resolve($container);
        $this->assertIsCallable($result);
    }
}
