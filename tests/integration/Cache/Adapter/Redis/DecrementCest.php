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
use Phalcon\Storage\Exception;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\Fixtures\Traits\RedisTrait;

use function getOptionsRedis;
use function getOptionsRedisCluster;
use function uniqid;

class DecrementCest
{
    use RedisTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Redis* :: decrement()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     *
     * @throws CacheException
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisDecrement(IntegrationTester $I, Example $example): void
    {
        $I->wantToTest(
            sprintf(
                'Cache\Adapter\%s - decrement()',
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

        $key      = uniqid();
        $expected = 100;
        $actual   = $adapter->increment($key, 100);
        $I->assertEquals($expected, $actual);

        $expected = 99;
        $actual   = $adapter->decrement($key);
        $I->assertEquals($expected, $actual);

        $actual = $adapter->get($key);
        $I->assertEquals($expected, $actual);

        $expected = 90;
        $actual   = $adapter->decrement($key, 9);
        $I->assertEquals($expected, $actual);

        $actual = $adapter->get($key);
        $I->assertEquals($expected, $actual);

        /**
         * unknown key
         */
        $key      = uniqid();
        $expected = -9;
        $actual   = $adapter->decrement($key, 9);
        $I->assertEquals($expected, $actual);
    }

    private function getExamples(): array
    {
        return [
            [
                'className' => 'Redis',
                'label'     => 'default',
                'class'     => Redis::class,
                'options'   => getOptionsRedis(),
                'extension' => 'redis',
            ],
            [
                'className' => 'RedisCluster',
                'label'     => 'default',
                'class'     => RedisCluster::class,
                'options'   => getOptionsRedisCluster(),
                'extension' => 'redis',
            ],
        ];
    }
}
