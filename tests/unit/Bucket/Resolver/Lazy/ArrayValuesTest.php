<?php

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Resolver\Lazy;

use ArrayIterator;
use Phalcon\Bucket\Resolver\Lazy\ArrayValues;
use Phalcon\Bucket\Resolver\Lazy\Get;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class ArrayValuesTest extends AbstractUnitTestCase
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
    public function testBucketResolverLazyArrayValuesArrayAccessSyntax(): void
    {
        $lazy        = new ArrayValues();
        $lazy['key'] = 'value';
        $this->assertTrue(isset($lazy['key']));
        $this->assertSame('value', $lazy['key']);
        unset($lazy['key']);
        $this->assertFalse(isset($lazy['key']));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesCount(): void
    {
        $lazy = new ArrayValues(['a' => 1, 'b' => 2, 'c' => 3]);
        $this->assertSame(3, $lazy->count());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesCountEmpty(): void
    {
        $lazy = new ArrayValues();
        $this->assertSame(0, $lazy->count());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesGetIterator(): void
    {
        $lazy = new ArrayValues(['x' => 10]);
        $this->assertInstanceOf(ArrayIterator::class, $lazy->getIterator());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesMergeIntKeys(): void
    {
        $lazy = new ArrayValues([1, 2]);
        $lazy->merge([3, 4]);
        $this->assertSame(4, $lazy->count());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesMergeStringKeys(): void
    {
        $lazy = new ArrayValues(['a' => 1]);
        $lazy->merge(['b' => 2]);
        $this->assertSame(2, $lazy->count());
        $this->assertTrue($lazy->offsetExists('b'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesOffsetExists(): void
    {
        $lazy = new ArrayValues(['key' => 'value']);
        $this->assertTrue($lazy->offsetExists('key'));
        $this->assertFalse($lazy->offsetExists('missing'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesOffsetGet(): void
    {
        $lazy = new ArrayValues(['foo' => 'bar']);
        $this->assertSame('bar', $lazy->offsetGet('foo'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesOffsetSetWithKey(): void
    {
        $lazy = new ArrayValues();
        $lazy->offsetSet('myKey', 'myValue');
        $this->assertTrue($lazy->offsetExists('myKey'));
        $this->assertSame('myValue', $lazy->offsetGet('myKey'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesOffsetSetWithNull(): void
    {
        $lazy = new ArrayValues();
        $lazy->offsetSet(null, 'appended');
        $this->assertSame(1, $lazy->count());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesOffsetUnset(): void
    {
        $lazy = new ArrayValues(['a' => 1, 'b' => 2]);
        $lazy->offsetUnset('a');
        $this->assertFalse($lazy->offsetExists('a'));
        $this->assertSame(1, $lazy->count());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesResolveLazyValues(): void
    {
        $container = $this->makeContainer();
        $lazy      = new ArrayValues(['service' => new Get('SomeService')]);
        $result    = $lazy->resolve($container);
        $this->assertArrayHasKey('service', $result);
        $this->assertInstanceOf(stdClass::class, $result['service']);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesResolveNestedArrayValues(): void
    {
        $container = $this->makeContainer();
        $lazy      = new ArrayValues(['nested' => ['a' => 1, 'b' => 2]]);
        $result    = $lazy->resolve($container);
        $this->assertSame(['nested' => ['a' => 1, 'b' => 2]], $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverLazyArrayValuesResolvePlainValues(): void
    {
        $container = $this->makeContainer();
        $lazy      = new ArrayValues(['x' => 42, 'y' => 'hello']);
        $result    = $lazy->resolve($container);
        $this->assertSame(['x' => 42, 'y' => 'hello'], $result);
    }
}
