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

namespace Phalcon\Tests\Integration\Cache\Adapter\Redis;

use Codeception\Example;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Storage\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use Phalcon\Tests\Fixtures\Traits\RedisTrait;
use stdClass;
use IntegrationTester;

use function array_merge;
use function getOptionsRedis;
use function uniqid;

class GetSetCest
{
    use RedisTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: get()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example    $example
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetSet(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Cache\Adapter\Redis - get()/set() - ' . $example[0]);

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Redis($helper, $serializer, getOptionsRedis());

        $key    = 'cache-data';
        $actual = $adapter->set($key, $example[1]);
        $I->assertTrue($actual);

        $expected = $example[1];
        $actual   = $adapter->get($key);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: get() - persistent
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetSetPersistent(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Redis - get()/set() - persistent');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Redis(
            $helper,
            $serializer,
            array_merge(
                getOptionsRedis(),
                [
                    'persistent' => true,
                ]
            )
        );

        $key    = uniqid();
        $actual = $adapter->set($key, 'test');
        $I->assertTrue($actual);

        $expected = 'test';
        $actual   = $adapter->get($key);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: get() - wrong index
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetSetWrongIndex(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Redis - get()/set() - wrong index');

        $I->expectThrowable(
            new CacheException('Redis server selected database failed'),
            function () {
                $helper     = new HelperFactory();
                $serializer = new SerializerFactory();
                $adapter    = new Redis(
                    $helper,
                    $serializer,
                    array_merge(
                        getOptionsRedis(),
                        [
                            'index' => 99,
                        ]
                    )
                );

                $adapter->get('test');
            }
        );
    }

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: get() - failed auth
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetSetFailedAuth(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Redis - get()/set() - failed auth');

        $I->expectThrowable(
            new CacheException('Failed to authenticate with the Redis server'),
            function () {
                $helper     = new HelperFactory();
                $serializer = new SerializerFactory();
                $adapter    = new Redis(
                    $helper,
                    $serializer,
                    array_merge(
                        getOptionsRedis(),
                        [
                            'auth' => 'something',
                        ]
                    )
                );

                $adapter->get('test');
            }
        );
    }

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: get()/set() - custom serializer
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetSetCustomSerializer(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Redis - get()/set() - custom serializer');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Redis(
            $helper,
            $serializer,
            array_merge(
                getOptionsRedis(),
                [
                    'defaultSerializer' => 'Base64',
                ]
            )
        );

        $key    = 'cache-data';
        $source = 'Phalcon Framework';

        $actual = $adapter->set($key, $source);
        $I->assertTrue($actual);

        $expected = $source;
        $actual   = $adapter->get($key);
        $I->assertEquals($expected, $actual);
    }

    private function getExamples(): array
    {
        return [
            [
                'string',
                'random string',
            ],
            [
                'integer',
                123456,
            ],
            [
                'float',
                123.456,
            ],
            [
                'boolean',
                true,
            ],
            [
                'object',
                new stdClass(),
            ],
        ];
    }
}
