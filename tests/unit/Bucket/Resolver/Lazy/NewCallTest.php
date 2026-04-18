<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Resolver\Lazy;

use Phalcon\Bucket\Resolver\Lazy\NewCall;
use Phalcon\Tests\AbstractUnitTestCase;

final class NewCallTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyNewCallInvokeDelegatesToResolve(): void
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

        $lazy   = new NewCall('SomeService', 'ping', []);
        $result = $lazy($container);
        $this->assertSame('pong', $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyNewCallResolveCallsMethodOnNewInstance(): void
    {
        $service = new class () {
            public function greet(string $name): string
            {
                return 'Hi, ' . $name;
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

        $lazy   = new NewCall('SomeService', 'greet', ['Alice']);
        $result = $lazy->resolve($container);
        $this->assertSame('Hi, Alice', $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyNewCallResolveWithNoArguments(): void
    {
        $service = new class () {
            public function value(): int
            {
                return 99;
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

        $lazy   = new NewCall('SomeService', 'value', []);
        $result = $lazy->resolve($container);
        $this->assertSame(99, $result);
    }
}
