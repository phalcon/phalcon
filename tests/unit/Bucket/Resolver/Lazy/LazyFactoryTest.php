<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Resolver\Lazy;

use Phalcon\Bucket\Resolver\Lazy\ArrayValues;
use Phalcon\Bucket\Resolver\Lazy\Call;
use Phalcon\Bucket\Resolver\Lazy\CallableGet;
use Phalcon\Bucket\Resolver\Lazy\CallableNew;
use Phalcon\Bucket\Resolver\Lazy\CsEnv;
use Phalcon\Bucket\Resolver\Lazy\Env;
use Phalcon\Bucket\Resolver\Lazy\FunctionCall;
use Phalcon\Bucket\Resolver\Lazy\Get;
use Phalcon\Bucket\Resolver\Lazy\GetCall;
use Phalcon\Bucket\Resolver\Lazy\LazyFactory;
use Phalcon\Bucket\Resolver\Lazy\NewCall;
use Phalcon\Bucket\Resolver\Lazy\NewInstance;
use Phalcon\Bucket\Resolver\Lazy\StaticCall;
use Phalcon\Tests\AbstractUnitTestCase;

final class LazyFactoryTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryArrayValues(): void
    {
        $values = ['a' => 1, 'b' => 2];
        $result = LazyFactory::arrayValues($values);
        $this->assertInstanceOf(ArrayValues::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryCall(): void
    {
        $callable = fn() => 'test';
        $result   = LazyFactory::call($callable);
        $this->assertInstanceOf(Call::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryCallableGet(): void
    {
        $result = LazyFactory::callableGet('SomeId');
        $this->assertInstanceOf(CallableGet::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryCallableNew(): void
    {
        $result = LazyFactory::callableNew('SomeId');
        $this->assertInstanceOf(CallableNew::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryCsEnv(): void
    {
        $result = LazyFactory::csEnv('APP_NAME', 'string');
        $this->assertInstanceOf(CsEnv::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryCsEnvWithoutType(): void
    {
        $result = LazyFactory::csEnv('APP_NAME');
        $this->assertInstanceOf(CsEnv::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryEnv(): void
    {
        $result = LazyFactory::env('APP_NAME', 'string');
        $this->assertInstanceOf(Env::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryEnvWithoutType(): void
    {
        $result = LazyFactory::env('APP_NAME');
        $this->assertInstanceOf(Env::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryFunctionCall(): void
    {
        $result = LazyFactory::functionCall('strlen', ['hello']);
        $this->assertInstanceOf(FunctionCall::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryGet(): void
    {
        $result = LazyFactory::get('SomeId');
        $this->assertInstanceOf(Get::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryGetCall(): void
    {
        $result = LazyFactory::getCall('SomeId', 'someMethod', []);
        $this->assertInstanceOf(GetCall::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryNewCall(): void
    {
        $result = LazyFactory::newCall('SomeId', 'someMethod', []);
        $this->assertInstanceOf(NewCall::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryNewInstance(): void
    {
        $result = LazyFactory::newInstance('SomeId');
        $this->assertInstanceOf(NewInstance::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyFactoryStaticCall(): void
    {
        $result = LazyFactory::staticCall('Some\\Class', 'someMethod', []);
        $this->assertInstanceOf(StaticCall::class, $result);
    }
}
