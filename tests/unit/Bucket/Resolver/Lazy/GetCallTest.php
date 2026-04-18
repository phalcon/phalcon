<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Resolver\Lazy;

use Phalcon\Bucket\Resolver\Lazy\GetCall;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetCallTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyGetCallInvokeDelegatesToResolve(): void
    {
        $service = new class () {
            public function ping(): string
            {
                return 'pong';
            }
        };

        $container = new class ($service) {
            public function __construct(
                private mixed $service
            ) {
            }

            public function get(string $id): mixed
            {
                return $this->service;
            }

            public function new(string $id): mixed
            {
                return $this->service;
            }
        };

        $lazy   = new GetCall('SomeService', 'ping', []);
        $result = $lazy($container);
        $this->assertSame('pong', $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyGetCallResolveCallsMethodOnService(): void
    {
        $service = new class () {
            public function greet(string $name): string
            {
                return 'Hello, ' . $name;
            }
        };

        $container = new class ($service) {
            public function __construct(
                private mixed $service
            ) {
            }

            public function get(string $id): mixed
            {
                return $this->service;
            }

            public function new(string $id): mixed
            {
                return $this->service;
            }
        };

        $lazy   = new GetCall('SomeService', 'greet', ['World']);
        $result = $lazy->resolve($container);
        $this->assertSame('Hello, World', $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyGetCallResolveWithNoArguments(): void
    {
        $service = new class () {
            public function value(): int
            {
                return 42;
            }
        };

        $container = new class ($service) {
            public function __construct(
                private mixed $service
            ) {
            }

            public function get(string $id): mixed
            {
                return $this->service;
            }

            public function new(string $id): mixed
            {
                return $this->service;
            }
        };

        $lazy   = new GetCall('SomeService', 'value', []);
        $result = $lazy->resolve($container);
        $this->assertSame(42, $result);
    }
}
