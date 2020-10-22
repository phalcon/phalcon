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

use Phalcon\Cache\Cache;
use Phalcon\Cache\AdapterFactory;
use Phalcon\Cache\Exception\InvalidArgumentException;
use Phalcon\Storage\SerializerFactory;
use IntegrationTester;

use Phalcon\Support\HelperFactory;
use function uniqid;

class SetMultipleCest
{
    /**
     * Tests Phalcon\Cache :: setMultiple()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cacheCacheSetMultiple(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Cache - setMultiple()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($helper, $serializer);
        $instance   = $factory->newInstance('apcu');

        $adapter = new Cache($instance);

        $key1 = uniqid();
        $key2 = uniqid();
        $adapter->setMultiple(
            [
                $key1 => 'test1',
                $key2 => 'test2',
            ]
        );

        $I->assertTrue($adapter->has($key1));
        $I->assertTrue($adapter->has($key2));

        $expected = [
            $key1     => 'test1',
            $key2     => 'test2',
            'unknown' => 'default-unknown',
        ];
        $actual   = $adapter->getMultiple([$key1, $key2, 'unknown'], 'default-unknown');
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache :: setMultiple() - exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function cacheCacheSetMultipleException(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Cache - setMultiple() - exception');

        $I->expectThrowable(
            new InvalidArgumentException('The key contains invalid characters'),
            function () {
                $helper     = new HelperFactory();
                $serializer = new SerializerFactory();
                $factory    = new AdapterFactory($helper, $serializer);
                $instance   = $factory->newInstance('apcu');

                $adapter = new Cache($instance);

                $adapter->setMultiple(
                    [
                        'abc$^' => 'test1',
                        'abd$^' => 'test2',
                    ]
                );
            }
        );

        $I->expectThrowable(
            new InvalidArgumentException(
                'The keys need to be an array or instance of Traversable'
            ),
            function () {
                $helper     = new HelperFactory();
                $serializer = new SerializerFactory();
                $factory    = new AdapterFactory($helper, $serializer);
                $instance   = $factory->newInstance('apcu');

                $adapter = new Cache($instance);

                $actual = $adapter->setMultiple(1234);
            }
        );
    }
}
