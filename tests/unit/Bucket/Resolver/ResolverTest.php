<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been heavily influenced by CapsulePHP.
 * Additionally, there are implementations from ioc-interop, which is a
 * Composer dependency, and from service-interop and resolver-interop. The
 * latter two are copied and re-implemented here: service-interop is not yet
 * published on Packagist, and resolver-interop requires PHP 8.4 (this project
 * targets PHP 8.1). Once both packages become available and compatible, the
 * copies will be replaced with the actual Composer dependencies.
 *
 * @link    https://github.com/capsulephp/di
 * @license https://github.com/capsulephp/di/blob/3.x/LICENSE.md
 *
 * @link    https://github.com/ioc-interop/interface
 * @license https://github.com/ioc-interop/interface/blob/1.x/LICENSE.md
 *
 * @link    https://github.com/service-interop/interface
 * @license https://github.com/service-interop/interface/blob/1.x/LICENSE.md
 *
 * @link    https://github.com/resolver-interop/interface/tree/1.x
 * @license https://github.com/resolver-interop/interface/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Bucket\Resolver;

use Phalcon\Bucket\Exception\Invalid;
use Phalcon\Bucket\Resolver\Lazy\Get;
use Phalcon\Bucket\Resolver\Resolver;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Bucket\Resolver\Fake\FakeServiceNoConstructor;
use Phalcon\Tests\Unit\Bucket\Resolver\Fake\FakeServiceWithArgs;
use Phalcon\Tests\Unit\Bucket\Resolver\Fake\FakeServiceWithOptional;
use Phalcon\Tests\Unit\Bucket\Resolver\Fake\FakeServiceWithTypedArg;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use stdClass;

final class ResolverTest extends AbstractUnitTestCase
{
    private function makeContainer(bool $hasService = false): object
    {
        return new class ($hasService) {
            public function __construct(private readonly bool $hasService)
            {
            }

            public function has(string $id): bool
            {
                return $this->hasService;
            }

            public function get(string $id): mixed
            {
                return new FakeServiceNoConstructor();
            }
        };
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolveCallWithNonClosureCallable(): void
    {
        $resolver  = new Resolver();
        $container = new class () {
            public function has(string $id): bool
            {
                return false;
            }
            public function get(string $id): mixed
            {
                return new \stdClass();
            }
        };

        $target = new class () {
            public function greet(string $name): string
            {
                return 'Hello ' . $name;
            }
        };

        $result = $resolver->resolveCall($container, [$target, 'greet'], ['name' => 'World']);
        $this->assertSame('Hello World', $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverIsResolvableClassReturnsFalseForInterface(): void
    {
        $resolver = new Resolver();
        $this->assertFalse($resolver->isResolvableClass(\Countable::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverIsResolvableClassReturnsFalseForNonExistentClass(): void
    {
        $resolver = new Resolver();
        $this->assertFalse($resolver->isResolvableClass('NonExistent\\Class\\That\\DoesNotExist'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverIsResolvableClassReturnsTrueForInstantiableClass(): void
    {
        $resolver = new Resolver();
        $this->assertTrue($resolver->isResolvableClass(FakeServiceNoConstructor::class));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveCallWithClosure(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer();

        $callable = static function (string $name, int $count): string {
            return $name . ':' . $count;
        };

        $result = $resolver->resolveCall($container, $callable, ['world', 42]);
        $this->assertSame('world:42', $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveCallWithNamedArgs(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer();

        $callable = static function (string $greeting, int $times): string {
            return str_repeat($greeting, $times);
        };

        $result = $resolver->resolveCall(
            $container,
            $callable,
            ['greeting' => 'hi', 'times' => 3]
        );
        $this->assertSame('hihihi', $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveClassNoConstructor(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer();
        $result    = $resolver->resolveClass($container, FakeServiceNoConstructor::class, []);
        $this->assertInstanceOf(FakeServiceNoConstructor::class, $result);
        $this->assertSame('default', $result->value);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveClassThrowsWhenRequiredParamUnresolvable(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer(false);

        $this->expectException(Invalid::class);
        $this->expectExceptionMessage("Cannot resolve parameter '\$host' for");

        $resolver->resolveClass($container, FakeServiceWithArgs::class, []);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveClassWithExplicitNamedArgs(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer();
        $result    = $resolver->resolveClass(
            $container,
            FakeServiceWithArgs::class,
            ['host' => 'db.local', 'port' => 3306]
        );
        $this->assertInstanceOf(FakeServiceWithArgs::class, $result);
        $this->assertSame('db.local', $result->host);
        $this->assertSame(3306, $result->port);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveClassWithExplicitPositionalArgs(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer();
        $result    = $resolver->resolveClass(
            $container,
            FakeServiceWithArgs::class,
            ['localhost', 5432]
        );
        $this->assertInstanceOf(FakeServiceWithArgs::class, $result);
        $this->assertSame('localhost', $result->host);
        $this->assertSame(5432, $result->port);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveClassWithOptionalDefaultsToDefault(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer();
        $result    = $resolver->resolveClass(
            $container,
            FakeServiceWithOptional::class,
            ['host' => 'mysql.local']
        );
        $this->assertInstanceOf(FakeServiceWithOptional::class, $result);
        $this->assertSame('mysql.local', $result->host);
        $this->assertSame(3306, $result->port);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveClassWithTypedArgFromContainer(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer(true);
        $result    = $resolver->resolveClass(
            $container,
            FakeServiceWithTypedArg::class,
            []
        );
        $this->assertInstanceOf(FakeServiceWithTypedArg::class, $result);
        $this->assertInstanceOf(FakeServiceNoConstructor::class, $result->dependency);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveMethodInvokesWithResolvedParams(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer(true);

        $target = new class () {
            public string $injected = '';

            public function setup(FakeServiceNoConstructor $svc): void
            {
                $this->injected = $svc->value;
            }
        };

        $method = (new ReflectionClass($target))->getMethod('setup');
        $resolver->resolveMethod($container, $method, $target);
        $this->assertSame('default', $target->injected);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveParametersResolvesLazyValues(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer(true);
        $params    = (new ReflectionClass(FakeServiceWithArgs::class))
            ->getConstructor()
            ->getParameters();

        $lazyHost = new Get(FakeServiceNoConstructor::class);
        $resolved = $resolver->resolveParameters($container, $params, [$lazyHost, 8080]);
        $this->assertInstanceOf(FakeServiceNoConstructor::class, $resolved[0]);
        $this->assertSame(8080, $resolved[1]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveParametersWithPositionalArgs(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer();
        $params    = (new ReflectionClass(FakeServiceWithArgs::class))
            ->getConstructor()
            ->getParameters();

        $resolved = $resolver->resolveParameters($container, $params, ['myhost', 9999]);
        $this->assertSame('myhost', $resolved[0]);
        $this->assertSame(9999, $resolved[1]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveTypeReturnsNameForNamedType(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer();
        $param     = (new ReflectionClass(FakeServiceWithTypedArg::class))
            ->getConstructor()
            ->getParameters()[0];
        $type      = $param->getType();

        $this->assertInstanceOf(ReflectionNamedType::class, $type);
        $result = $resolver->resolveType($container, $type);
        $this->assertSame(FakeServiceNoConstructor::class, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolverResolverResolveTypeReturnsNullForUnionType(): void
    {
        $resolver  = new Resolver();
        $container = $this->makeContainer();

        $target = new class () {
            public function method(int|string $value): void
            {
            }
        };

        $param = (new ReflectionClass($target))->getMethod('method')->getParameters()[0];
        $type  = $param->getType();

        $this->assertInstanceOf(ReflectionUnionType::class, $type);
        $result = $resolver->resolveType($container, $type);
        $this->assertNull($result);
    }
}
