<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Resolver\Lazy;

use Phalcon\Bucket\Exception\NotFound;
use Phalcon\Bucket\Resolver\Lazy\CsEnv;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class CsEnvTest extends AbstractUnitTestCase
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
        unset($_ENV['PHALCON_TEST_CSV_VAR']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        unset($_ENV['PHALCON_TEST_CSV_VAR']);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyCsEnvResolveCastsToFloat(): void
    {
        $_ENV['PHALCON_TEST_CSV_VAR'] = '1.1,2.2,3.3';
        $container                    = $this->makeContainer();
        $lazy                         = new CsEnv('PHALCON_TEST_CSV_VAR', 'float');
        $result                       = $lazy->resolve($container);
        $this->assertSame([1.1, 2.2, 3.3], $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyCsEnvResolveCastsToInt(): void
    {
        $_ENV['PHALCON_TEST_CSV_VAR'] = '1,2,3';
        $container                    = $this->makeContainer();
        $lazy                         = new CsEnv('PHALCON_TEST_CSV_VAR', 'int');
        $result                       = $lazy->resolve($container);
        $this->assertSame([1, 2, 3], $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyCsEnvResolveHandlesQuotedValues(): void
    {
        $_ENV['PHALCON_TEST_CSV_VAR'] = '"hello world",foo,"bar"';
        $container                    = $this->makeContainer();
        $lazy                         = new CsEnv('PHALCON_TEST_CSV_VAR');
        $result                       = $lazy->resolve($container);
        $this->assertSame(['hello world', 'foo', 'bar'], $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyCsEnvResolveParsesCSV(): void
    {
        $_ENV['PHALCON_TEST_CSV_VAR'] = 'a,b,c';
        $container                    = $this->makeContainer();
        $lazy                         = new CsEnv('PHALCON_TEST_CSV_VAR');
        $result                       = $lazy->resolve($container);
        $this->assertSame(['a', 'b', 'c'], $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyCsEnvResolveReturnsSingleElement(): void
    {
        $_ENV['PHALCON_TEST_CSV_VAR'] = 'onlyone';
        $container                    = $this->makeContainer();
        $lazy                         = new CsEnv('PHALCON_TEST_CSV_VAR');
        $result                       = $lazy->resolve($container);
        $this->assertSame(['onlyone'], $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyCsEnvResolveThrowsNotFoundForMissingVar(): void
    {
        $container = $this->makeContainer();
        $lazy      = new CsEnv('PHALCON_UNDEFINED_CSV_ENV_VAR_XYZ');

        $this->expectException(NotFound::class);
        $this->expectExceptionMessage(
            "Environment variable 'PHALCON_UNDEFINED_CSV_ENV_VAR_XYZ' is not defined"
        );

        $lazy->resolve($container);
    }
}
