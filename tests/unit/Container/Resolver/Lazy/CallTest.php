<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Container\Resolver\Lazy;

use Phalcon\Container\Resolver\Lazy\Call;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class CallTest extends AbstractUnitTestCase
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
    public function testContainerResolverLazyCallInvokesDelegateToResolve(): void
    {
        $container = $this->makeContainer();
        $expected  = new stdClass();
        $lazy      = new Call(static function (object $c) use ($expected) {
            return $expected;
        });

        $result = $lazy($container);
        $this->assertSame($expected, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testContainerResolverLazyCallReceivesContainer(): void
    {
        $container = $this->makeContainer();
        $received  = null;
        $lazy      = new Call(static function (object $c) use (&$received) {
            $received = $c;
            return null;
        });

        $lazy->resolve($container);
        $this->assertSame($container, $received);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testContainerResolverLazyCallResolveInvokesCallable(): void
    {
        $container = $this->makeContainer();
        $expected  = new stdClass();
        $lazy      = new Call(static function (object $c) use ($expected) {
            return $expected;
        });

        $result = $lazy->resolve($container);
        $this->assertSame($expected, $result);
    }
}
