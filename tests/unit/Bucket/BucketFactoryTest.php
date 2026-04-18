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

use IocInterop\Interface\IocContainer;
use Phalcon\Bucket\Bucket;
use Phalcon\Bucket\BucketFactory;
use Phalcon\Tests\Unit\Bucket\Fake\FakeServiceProvider;
use PHPUnit\Framework\TestCase;

class BucketFactoryTest extends TestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testAddProviderIsChainable(): void
    {
        $factory  = new BucketFactory();
        $provider = new FakeServiceProvider();

        $result = $factory->addProvider($provider);

        $this->assertSame($factory, $result);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testNewBucketReturnsABucket(): void
    {
        $factory = new BucketFactory();
        $bucket  = $factory->newBucket();

        $this->assertInstanceOf(Bucket::class, $bucket);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testNewBucketReturnsDifferentInstances(): void
    {
        $factory  = new BucketFactory();
        $bucket1  = $factory->newBucket();
        $bucket2  = $factory->newBucket();

        $this->assertNotSame($bucket1, $bucket2);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testNewContainerReturnsABucket(): void
    {
        $factory = new BucketFactory();
        $bucket  = $factory->newContainer();

        $this->assertInstanceOf(IocContainer::class, $bucket);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testProviderIsCalledAndRegistersServices(): void
    {
        $factory = new BucketFactory();
        $factory->addProvider(new FakeServiceProvider());

        $bucket = $factory->newBucket();

        $this->assertTrue($bucket->hasDefinition('fake'));
    }
}
