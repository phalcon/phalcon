<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Container\Resolver\Lazy;

use Phalcon\Container\Resolver\Lazy\EnvDefault;
use Phalcon\Container\Resolver\Lazy\LazyFactory;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class EnvDefaultTest extends AbstractUnitTestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        unset($_ENV['PHALCON_TEST_VAR']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($_ENV['PHALCON_TEST_VAR']);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testContainerResolverLazyEnvDefaultResolveReturnsDefaultWhenNotDefined(): void
    {
        $container = $this->makeContainer();
        $lazy      = new EnvDefault('PHALCON_TEST_VAR', 'fallback');

        $this->assertSame('fallback', $lazy->resolve($container));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testContainerResolverLazyEnvDefaultResolveReturnsDefaultIntWhenNotDefined(): void
    {
        $container = $this->makeContainer();
        $lazy      = new EnvDefault('PHALCON_TEST_VAR', 3306);

        $this->assertSame(3306, $lazy->resolve($container));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testContainerResolverLazyEnvDefaultResolveReturnsDefaultNullWhenNotDefined(): void
    {
        $container = $this->makeContainer();
        $lazy      = new EnvDefault('PHALCON_TEST_VAR', null);

        $this->assertNull($lazy->resolve($container));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testContainerResolverLazyEnvDefaultResolveReturnsEnvValueWhenDefined(): void
    {
        $_ENV['PHALCON_TEST_VAR'] = 'hello';
        $container                = $this->makeContainer();
        $lazy                     = new EnvDefault('PHALCON_TEST_VAR', 'fallback');

        $this->assertSame('hello', $lazy->resolve($container));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testContainerResolverLazyEnvDefaultResolveCastsEnvValueWhenDefined(): void
    {
        $_ENV['PHALCON_TEST_VAR'] = '42';
        $container                = $this->makeContainer();
        $lazy                     = new EnvDefault('PHALCON_TEST_VAR', 0, 'int');

        $this->assertSame(42, $lazy->resolve($container));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testContainerResolverLazyEnvDefaultDefaultIsNotCastWhenNotDefined(): void
    {
        $container = $this->makeContainer();
        $lazy      = new EnvDefault('PHALCON_TEST_VAR', 99, 'string');

        // Default is returned as-is — type casting only applies to the env value
        $this->assertSame(99, $lazy->resolve($container));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testContainerResolverLazyEnvDefaultFactoryCreatesInstance(): void
    {
        $container = $this->makeContainer();
        $lazy      = LazyFactory::envDefault('PHALCON_TEST_VAR', 'fallback');

        $this->assertInstanceOf(EnvDefault::class, $lazy);
        $this->assertSame('fallback', $lazy->resolve($container));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-19
     */
    public function testContainerResolverLazyEnvDefaultFactoryWithTypeCreatesInstance(): void
    {
        $_ENV['PHALCON_TEST_VAR'] = '1';
        $container                = $this->makeContainer();
        $lazy                     = LazyFactory::envDefault('PHALCON_TEST_VAR', false, 'bool');

        $this->assertSame(true, $lazy->resolve($container));
    }
}
