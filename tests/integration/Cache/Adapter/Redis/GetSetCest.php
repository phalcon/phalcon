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
use IntegrationTester;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\RedisCluster;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\Fixtures\Traits\RedisTrait;
use stdClass;

use function array_merge;
use function getOptionsRedis;
use function getOptionsRedisCluster;
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
     * @param Example           $example
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function storageAdapterRedisGetSet(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Cache\Adapter\Redis - get()/set() - ' . $example[0]);

        $serializer = new SerializerFactory();
        $adapter    = new Redis($serializer, getOptionsRedis());

        $key    = uniqid();
        $actual = $adapter->set($key, $example[1]);
        $I->assertTrue($actual);

        $expected = $example[1];
        $actual   = $adapter->get($key);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\RedisCluster :: get()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function storageAdapterRedisClusterGetSet(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Cache\Adapter\Redis - get()/set() - ' . $example[0]);

        $serializer = new SerializerFactory();
        $adapter    = new RedisCluster($serializer, getOptionsRedisCluster());

        $key    = uniqid();
        $actual = $adapter->set($key, $example[1]);
        $I->assertTrue($actual);

        $expected = $example[1];
        $actual   = $adapter->get($key);
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: get() - persistent
     *
     * @dataProvider getAdapterExamples
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetSetPersistent(IntegrationTester $I, Example $example)
    {
        $I->wantToTest(
            sprintf(
                'Cache\Adapter\%s - get()/set() - persistent',
                $example['className']
            )
        );

        $extension = $example['extension'];
        $class     = $example['class'];
        $options   = $example['options'];

        if (!empty($extension)) {
            $I->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class(
            $serializer,
            array_merge(
                $options,
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
            new StorageException('Redis server selected database failed'),
            function () {
                $serializer = new SerializerFactory();
                $adapter    = new Redis(
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
     * @dataProvider getAdapterExamples
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetSetFailedAuth(IntegrationTester $I, Example $example)
    {
        $I->wantToTest(
            sprintf(
                'Cache\Adapter\%s - get()/set() - failed auth',
                $example['className']
            )
        );

        $extension = $example['extension'];
        $class     = $example['class'];
        $options   = $example['options'];

        if (!empty($extension)) {
            $I->checkExtensionIsLoaded($extension);
        }

        $I->expectThrowable(
            StorageException::class,
            function () use ($options, $class) {
                $serializer = new SerializerFactory();
                $adapter    = new $class(
                    $serializer,
                    array_merge(
                        $options,
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
     * @dataProvider getAdapterExamples
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetSetCustomSerializer(IntegrationTester $I, Example $example): void
    {
        $I->wantToTest(
            sprintf(
                'Cache\Adapter\%s - get()/set() - custom serializer',
                $example['className']
            )
        );

        $extension = $example['extension'];
        $class     = $example['class'];
        $options   = $example['options'];

        if (!empty($extension)) {
            $I->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class(
            $serializer,
            array_merge(
                $options,
                [
                    'defaultSerializer' => 'Base64',
                ]
            )
        );

        $key    = uniqid();
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

    private function getAdapterExamples(): array
    {
        return [
            [
                'className' => 'Redis',
                'label' => 'default',
                'class' => Redis::class,
                'options' => getOptionsRedis(),
                'extension' => 'redis',
            ],
            [
                'className' => 'RedisCluster',
                'label' => 'default',
                'class' => RedisCluster::class,
                'options' => getOptionsRedisCluster(),
                'extension' => 'redis',
            ],
        ];
    }
}
