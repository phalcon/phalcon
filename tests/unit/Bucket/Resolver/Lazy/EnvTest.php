<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Resolver\Lazy;

use Phalcon\Bucket\Exception\NotFound;
use Phalcon\Bucket\Resolver\Lazy\Env;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class EnvTest extends AbstractUnitTestCase
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
     * @since  2026-04-18
     */
    public function testBucketResolverLazyEnvResolveCastsToBool(): void
    {
        $_ENV['PHALCON_TEST_VAR'] = '1';
        $container                = $this->makeContainer();
        $lazy                     = new Env('PHALCON_TEST_VAR', 'bool');
        $result                   = $lazy->resolve($container);
        $this->assertTrue($result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyEnvResolveCastsToFloat(): void
    {
        $_ENV['PHALCON_TEST_VAR'] = '3.14';
        $container                = $this->makeContainer();
        $lazy                     = new Env('PHALCON_TEST_VAR', 'float');
        $result                   = $lazy->resolve($container);
        $this->assertSame(3.14, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyEnvResolveCastsToInt(): void
    {
        $_ENV['PHALCON_TEST_VAR'] = '42';
        $container                = $this->makeContainer();
        $lazy                     = new Env('PHALCON_TEST_VAR', 'int');
        $result                   = $lazy->resolve($container);
        $this->assertSame(42, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyEnvResolveNoTypecastReturnsString(): void
    {
        $_ENV['PHALCON_TEST_VAR'] = '99';
        $container                = $this->makeContainer();
        $lazy                     = new Env('PHALCON_TEST_VAR');
        $result                   = $lazy->resolve($container);
        $this->assertIsString($result);
        $this->assertSame('99', $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyEnvResolveReturnsEnvValue(): void
    {
        $_ENV['PHALCON_TEST_VAR'] = 'hello';
        $container                = $this->makeContainer();
        $lazy                     = new Env('PHALCON_TEST_VAR');
        $result                   = $lazy->resolve($container);
        $this->assertSame('hello', $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyEnvResolveThrowsNotFoundForMissingVar(): void
    {
        $container = $this->makeContainer();
        $lazy      = new Env('PHALCON_UNDEFINED_ENV_VAR_XYZ');

        $this->expectException(NotFound::class);
        $this->expectExceptionMessage(
            "Environment variable 'PHALCON_UNDEFINED_ENV_VAR_XYZ' is not defined"
        );

        $lazy->resolve($container);
    }
}
