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

namespace Phalcon\Tests\Unit\Bucket;

use Closure;
use Phalcon\Bucket\Bucket;
use Phalcon\Bucket\Definition\ServiceDefinition;
use Phalcon\Bucket\Definition\ServiceLifetime;
use Phalcon\Bucket\Exception\Invalid;
use Phalcon\Bucket\Exception\NotFound;
use Phalcon\Bucket\Resolver\Lazy\Env;
use Phalcon\Bucket\Service\Collection;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Unit\Bucket\Fake\FakeService;
use Phalcon\Tests\Unit\Bucket\Fake\FakeServiceProvider;
use Phalcon\Tests\Unit\Bucket\Fake\FakeServiceWithDependency;
use stdClass;

final class BucketTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketAliasChainDoesNotFalselyDetectCircular(): void
    {
        $bucket = new Bucket();
        $bucket->set('a', FakeService::class);
        $bucket->set('b', FakeService::class);
        $bucket->set('c', FakeService::class);
        $bucket->set('d', FakeService::class);
        $bucket->setAlias('a', 'b');
        $bucket->setAlias('b', 'c');
        $bucket->setAlias('c', 'd');
        $this->assertInstanceOf(FakeService::class, $bucket->get('d'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketAutowiresConstructorDependencies(): void
    {
        $bucket = new Bucket();
        $bucket->set(FakeService::class, FakeService::class);
        $bucket->set(FakeServiceWithDependency::class, FakeServiceWithDependency::class);

        $instance = $bucket->get(FakeServiceWithDependency::class);

        $this->assertInstanceOf(FakeServiceWithDependency::class, $instance);
        $this->assertInstanceOf(FakeService::class, $instance->service);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketAutowiresConstructorDependenciesNoDefinitions(): void
    {
        $bucket = new Bucket();

        $instance = $bucket->get(FakeServiceWithDependency::class);

        $this->assertInstanceOf(FakeServiceWithDependency::class, $instance);
        $this->assertInstanceOf(FakeService::class, $instance->service);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketBindRegistersDefinition(): void
    {
        $bucket = new Bucket();
        $def    = $bucket->bind('iface', FakeService::class);

        $this->assertInstanceOf(ServiceDefinition::class, $def);
        $this->assertTrue($bucket->has('iface'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketCallableGetReturnsClosure(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);

        $callable = $bucket->callableGet('fake');

        $this->assertInstanceOf(Closure::class, $callable);
        $this->assertInstanceOf(FakeService::class, $callable());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketCallableNewReturnsClosureReturningDifferentInstances(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);

        $callable = $bucket->callableNew('fake');

        $this->assertInstanceOf(Closure::class, $callable);
        $this->assertNotSame($callable(), $callable());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketCircularAliasThrowsInvalid(): void
    {
        $bucket = new Bucket();
        $bucket->set('a', FakeService::class);
        $bucket->setAlias('a', 'b');

        $this->expectException(Invalid::class);

        $bucket->setAlias('b', 'a');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketDetectCircularAliasSeenBreak(): void
    {
        $bucket = new Bucket();
        // Inject a pre-existing cycle (bypassing setAlias protection)
        $this->setProtectedProperty($bucket, 'aliases', ['b' => 'a', 'a' => 'b']);
        // detectCircularAlias('new_alias', 'a') follows a→b→a and hits the seen break
        $bucket->setAlias('a', 'new_alias');
        // If we reach here without throwing, the break was hit correctly
        $this->assertTrue($bucket->hasAlias('new_alias'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExtendModifiesService(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);
        $bucket->extend('fake', static function (FakeService $svc): FakeService {
            $svc->value = 'extended';

            return $svc;
        });

        $instance = $bucket->get('fake');

        $this->assertSame('extended', $instance->value);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExtendThrowsForUnregisteredService(): void
    {
        $bucket = new Bucket();
        $this->expectException(NotFound::class);
        $bucket->extend('unknown', static function (object $svc, object $c) {
            return $svc;
        });
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketExtendThrowsWhenAlreadyResolved(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);
        $bucket->get('fake');

        $this->expectException(Invalid::class);

        $bucket->extend('fake', static function (FakeService $svc): FakeService {
            return $svc;
        });
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketFindProcessorThrowsForUnprocessable(): void
    {
        $bucket = new Bucket();
        $this->expectException(Invalid::class);
        $bucket->set('svc', 42);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetByTagReturnsEmptyArrayForUnknownTag(): void
    {
        $bucket = new Bucket();

        $this->assertSame([], $bucket->getByTag('unknown-tag'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetByTagReturnsResolvedInstances(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class)->addTag('my-tag');

        $result = $bucket->getByTag('my-tag');

        $this->assertCount(1, $result);
        $this->assertInstanceOf(FakeService::class, $result[0]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetDefinitionReturnsServiceDefinition(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);

        $def = $bucket->getDefinition('fake');

        $this->assertInstanceOf(ServiceDefinition::class, $def);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetDefinitionThrowsForMissing(): void
    {
        $bucket = new Bucket();

        $this->expectException(NotFound::class);

        $bucket->getDefinition('missing');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetInstanceThrowsForMissing(): void
    {
        $bucket = new Bucket();

        $this->expectException(NotFound::class);

        $bucket->getInstance('missing');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetInvokesClosure(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', static function (): FakeService {
            $svc        = new FakeService();
            $svc->value = 'from-closure';

            return $svc;
        });

        $instance = $bucket->get('fake');

        $this->assertInstanceOf(FakeService::class, $instance);
        $this->assertSame('from-closure', $instance->value);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetParameterThrowsForMissing(): void
    {
        $bucket = new Bucket();

        $this->expectException(NotFound::class);

        $bucket->getParameter('missing');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetResolvesParameterByName(): void
    {
        $bucket = new Bucket();
        $bucket->setParameter('app.name', 'Phalcon');

        $this->assertSame('Phalcon', $bucket->get('app.name'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetResolvesViaAlias(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);
        $bucket->setAlias('fake', 'alias');

        $instance = $bucket->get('alias');

        $this->assertInstanceOf(FakeService::class, $instance);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetReturnsInstance(): void
    {
        $bucket   = new Bucket();
        $bucket->set('fake', FakeService::class);
        $instance = $bucket->get('fake');

        $this->assertInstanceOf(FakeService::class, $instance);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetReturnsSameInstanceOnSubsequentCalls(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);

        $first  = $bucket->get('fake');
        $second = $bucket->get('fake');

        $this->assertSame($first, $second);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetReturnsSameObjectWhenRegisteredAsObject(): void
    {
        $bucket   = new Bucket();
        $service  = new FakeService();
        $bucket->set('fake', $service);

        $this->assertSame($service, $bucket->get('fake'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetServiceReturnsObject(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);

        $instance = $bucket->getService('fake');

        $this->assertInstanceOf(FakeService::class, $instance);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketGetServiceThrowsForNonObjectParameter(): void
    {
        $bucket = new Bucket();
        $bucket->setParameter('scalar', 'not-an-object');

        $this->expectException(Invalid::class);

        $bucket->getService('scalar');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketHasAliasTrueAfterSet(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);
        $bucket->setAlias('fake', 'alias');

        $this->assertTrue($bucket->hasAlias('alias'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketHasFalseForUnknown(): void
    {
        $bucket = new Bucket();

        $this->assertFalse($bucket->has('nonexistent'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketHasInstanceFalseBeforeSet(): void
    {
        $bucket = new Bucket();

        $this->assertFalse($bucket->hasInstance('missing'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketHasInstanceTrueAfterSet(): void
    {
        $bucket   = new Bucket();
        $instance = new FakeService();
        $bucket->setInstance('fake', $instance, ServiceLifetime::SINGLETON);

        $this->assertTrue($bucket->hasInstance('fake'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketHasParameterFalseBeforeSet(): void
    {
        $bucket = new Bucket();

        $this->assertFalse($bucket->hasParameter('missing'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketHasParameterTrueAfterSet(): void
    {
        $bucket = new Bucket();
        $bucket->setParameter('key', 'value');

        $this->assertTrue($bucket->hasParameter('key'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketHasServiceDelegatesToHas(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);

        $this->assertTrue($bucket->hasService('fake'));
        $this->assertFalse($bucket->hasService('nonexistent'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketHasTrueAfterSet(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);

        $this->assertTrue($bucket->has('fake'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketImplementsCollection(): void
    {
        $bucket = new Bucket();

        $this->assertInstanceOf(Collection::class, $bucket);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketImplicitAutowiringOfKnownClass(): void
    {
        $bucket   = new Bucket();
        $instance = $bucket->get(FakeService::class);

        $this->assertInstanceOf(FakeService::class, $instance);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketIsAutowireEnabledDefaultsToTrue(): void
    {
        $bucket = new Bucket();

        $this->assertTrue($bucket->isAutowireEnabled());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketNewDefinitionReturnsServiceDefinition(): void
    {
        $bucket = new Bucket();
        $def    = $bucket->newDefinition('test');
        $this->assertInstanceOf(ServiceDefinition::class, $def);
        $this->assertSame('test', $def->getServiceName());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketNewReturnsDifferentInstanceEachCall(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);

        $first  = $bucket->new('fake');
        $second = $bucket->new('fake');

        $this->assertNotSame($first, $second);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketParameterWithLazyEnvValue(): void
    {
        $_ENV['BUCKET_TEST_KEY'] = 'hello-env';

        $bucket = new Bucket();
        $bucket->setParameter('app.key', new Env('BUCKET_TEST_KEY'));

        $this->assertSame('hello-env', $bucket->getParameter('app.key'));

        unset($_ENV['BUCKET_TEST_KEY']);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketResolveAliasThrowsOnCyclicChain(): void
    {
        $bucket = new Bucket();
        // Inject a pre-existing cycle to trigger the safety-net in resolveAlias()
        $this->setProtectedProperty($bucket, 'aliases', ['a' => 'b', 'b' => 'a']);
        $this->expectException(Invalid::class);
        $bucket->get('a');
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketSetAliasAndGetAlias(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);
        $bucket->setAlias('fake', 'alias');

        $this->assertSame('fake', $bucket->getAlias('alias'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketSetAutowireFalseDisablesImplicitAutowiring(): void
    {
        $bucket = new Bucket();
        $bucket->setAutowire(false);

        $this->expectException(NotFound::class);

        $bucket->get(FakeService::class);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketSetAutowireFalseReturnsDisabled(): void
    {
        $bucket = new Bucket();
        $bucket->setAutowire(false);

        $this->assertFalse($bucket->isAutowireEnabled());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketSetDefinitionOverwrites(): void
    {
        $bucket  = new Bucket();
        $bucket->set('fake', FakeService::class);
        $newDef  = new ServiceDefinition('fake', 'string');
        $bucket->setDefinition('fake', $newDef);

        $this->assertSame($newDef, $bucket->getDefinition('fake'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketSetInstanceAndGetInstance(): void
    {
        $bucket   = new Bucket();
        $instance = new FakeService();
        $bucket->setInstance('fake', $instance, ServiceLifetime::SINGLETON);

        $this->assertSame($instance, $bucket->getInstance('fake'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketSetParameterAndGetParameter(): void
    {
        $bucket = new Bucket();
        $bucket->setParameter('db.host', 'localhost');

        $this->assertSame('localhost', $bucket->getParameter('db.host'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketSetReturnsServiceDefinition(): void
    {
        $bucket = new Bucket();
        $def    = $bucket->set('fake', FakeService::class);

        $this->assertInstanceOf(ServiceDefinition::class, $def);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketTransientLifetimeReturnsNewInstanceEachGet(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class)->setLifetime(ServiceLifetime::TRANSIENT);

        $first  = $bucket->get('fake');
        $second = $bucket->get('fake');

        $this->assertNotSame($first, $second);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketUnsetAlias(): void
    {
        $bucket = new Bucket();
        $bucket->set('original', FakeService::class);
        $bucket->setAlias('original', 'alias');
        $this->assertTrue($bucket->hasAlias('alias'));
        $bucket->unsetAlias('alias');
        $this->assertFalse($bucket->hasAlias('alias'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketUnsetDefinitionRemovesIt(): void
    {
        $bucket = new Bucket();
        $bucket->set('fake', FakeService::class);
        $bucket->unsetDefinition('fake');

        $this->assertFalse($bucket->hasDefinition('fake'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketUnsetInstanceRemovesIt(): void
    {
        $bucket   = new Bucket();
        $instance = new FakeService();
        $bucket->setInstance('fake', $instance, ServiceLifetime::SINGLETON);
        $bucket->unsetInstance('fake');

        $this->assertFalse($bucket->hasInstance('fake'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketUnsetInstancesRemovesOnlyMatchingLifetime(): void
    {
        $bucket    = new Bucket();
        $singleton = new FakeService();
        $scoped    = new FakeService();

        $bucket->setInstance('singleton', $singleton, ServiceLifetime::SINGLETON);
        $bucket->setInstance('scoped', $scoped, ServiceLifetime::SCOPED);

        $bucket->unsetInstances(ServiceLifetime::SCOPED);

        $this->assertTrue($bucket->hasInstance('singleton'));
        $this->assertFalse($bucket->hasInstance('scoped'));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testBucketUnsetParameterRemovesIt(): void
    {
        $bucket = new Bucket();
        $bucket->setParameter('key', 'value');
        $bucket->unsetParameter('key');

        $this->assertFalse($bucket->hasParameter('key'));
    }
}
