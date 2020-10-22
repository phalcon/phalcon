<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Integration\Cache\Cache;

use IntegrationTester;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Cache\Cache;
use Phalcon\Cache\Exception\InvalidArgumentException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use function uniqid;

class DeleteCest
{
    /**
     * Tests Phalcon\Cache :: delete()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cacheCacheDelete(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Cache - delete()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($helper, $serializer);
        $instance   = $factory->newInstance('apcu');

        $adapter = new Cache($instance);


        $key1 = uniqid();
        $key2 = uniqid();

        $adapter->set($key1, 'test');
        $I->assertTrue($adapter->has($key1));

        $adapter->set($key2, 'test');
        $I->assertTrue($adapter->has($key2));
        $I->assertTrue($adapter->delete($key1));
        $I->assertFalse($adapter->has($key1));
        $I->assertTrue($adapter->has($key2));
    }

    /**
     * Tests Phalcon\Cache :: delete() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cacheCacheDeleteException(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Cache - delete() - exception');

        $I->expectThrowable(
            new InvalidArgumentException('The key contains invalid characters'),
            function () {
                $helper     = new HelperFactory();
                $serializer = new SerializerFactory();
                $factory    = new AdapterFactory($helper, $serializer);
                $instance   = $factory->newInstance('apcu');

                $adapter = new Cache($instance);
                $value   = $adapter->delete('abc$^');
            }
        );
    }
}
