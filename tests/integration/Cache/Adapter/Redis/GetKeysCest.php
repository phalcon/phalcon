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
use Phalcon\Cache\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\Fixtures\Traits\RedisTrait;

use function getOptionsRedis;
use function getOptionsRedisCluster;

class GetKeysCest
{
    use RedisTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Redis* :: getKeys()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetKeys(IntegrationTester $I, Example $example): void
    {
        $I->wantToTest(
            sprintf(
                'Cache\Adapter\%s - getKeys()',
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
        $adapter    = new $class($serializer, $options);

        $I->assertTrue($adapter->clear());

        $adapter->set('key-1', 'test');
        $adapter->set('key-2', 'test');
        $adapter->set('one-1', 'test');
        $adapter->set('one-2', 'test');

        $actual = $adapter->has('key-1');
        $I->assertTrue($actual);
        $actual = $adapter->has('key-2');
        $I->assertTrue($actual);
        $actual = $adapter->has('one-1');
        $I->assertTrue($actual);
        $actual = $adapter->has('one-2');
        $I->assertTrue($actual);

        $prefix = $example['prefix'];
        $expected = [
            "{$prefix}-key-1",
            "{$prefix}-key-2",
            "{$prefix}-one-1",
            "{$prefix}-one-2",
        ];
        $actual   = $adapter->getKeys();
        sort($actual);
        $I->assertEquals($expected, $actual);

        $expected = [
            "{$prefix}-one-1",
            "{$prefix}-one-2",
        ];

        $actual   = $adapter->getKeys("one");
        sort($actual);
        $I->assertEquals($expected, $actual);
    }

    private function getExamples(): array
    {
        return [
            [
                'className' => 'Redis',
                'label' => 'default',
                'class' => Redis::class,
                'options' => getOptionsRedis(),
                'extension' => 'redis',
                'prefix' => 'ph-reds',
            ],
            [
                'className' => 'RedisCluster',
                'label' => 'default',
                'class' => RedisCluster::class,
                'options' => getOptionsRedisCluster(),
                'extension' => 'redis',
                'prefix' => 'ph-redc',
            ],
        ];
    }
}
