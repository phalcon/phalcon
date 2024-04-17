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

namespace Phalcon\Tests\Integration\Storage\Adapter;

use Codeception\Example;
use IntegrationTester;
use Phalcon\Storage\Adapter\Redis;
use Phalcon\Storage\Adapter\RedisCluster;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;

use function array_merge;
use function file_put_contents;
use function getOptionsRedis;
use function getOptionsRedisCluster;
use function is_dir;
use function mkdir;
use function outputDir;
use function sleep;
use function uniqid;

class ExceptionsCest
{
    /**
     * Tests Phalcon\Storage\Adapter\Redis :: get() - wrong index
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterRedisGetSetWrongIndex(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Redis - get()/set() - wrong index');

        $I->checkExtensionIsLoaded('redis');

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
     * Tests Phalcon\Storage\Adapter\Redis :: get() - failed auth
     *
     * @dataProvider getExamples
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
                'Storage\Adapter\%s - get()/set() - failed auth',
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
            function () use ($class, $options) {
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
     * Tests Phalcon\Storage\Adapter\Stream :: get() - errors
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamGetErrors(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - get() - errors');

        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $target = outputDir() . 'ph-strm/te/st/-k/';
        if (true !== is_dir($target)) {
            mkdir($target, 0777, true);
        }

        // Unknown key
        $expected = 'test';
        $actual   = $adapter->get(uniqid(), 'test');
        $I->assertSame($expected, $actual);

        // Invalid stored object
        $actual = file_put_contents(
            $target . 'test-key',
            '{'
        );
        $I->assertNotFalse($actual);

        $expected = 'test';
        $actual   = $adapter->get('test-key', 'test');
        $I->assertSame($expected, $actual);

        // Expiry
        $data = 'Phalcon Framework';

        $actual = $adapter->set('test-key', $data, 1);
        $I->assertTrue($actual);

        sleep(2);

        $expected = 'test';
        $actual   = $adapter->get('test-key', 'test');
        $I->assertSame($expected, $actual);

        $I->safeDeleteFile($target . 'test-key');
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
