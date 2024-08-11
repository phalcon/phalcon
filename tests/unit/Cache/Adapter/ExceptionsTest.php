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

namespace Phalcon\Tests\Unit\Cache\Adapter;

use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Tests\AbstractUnitTestCase;

use function array_merge;
use function file_put_contents;
use function getOptionsRedis;
use function is_dir;
use function mkdir;
use function outputDir;
use function sleep;
use function uniqid;

final class ExceptionsTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Cache\Adapter\Redis :: get() - failed auth
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterRedisGetSetFailedAuth(): void
    {
        $this->checkExtensionIsLoaded('redis');

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage(
            'Failed to authenticate with the Redis server'
        );

        $serializer = new SerializerFactory();
        $adapter    = new Redis(
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

    /**
     * Tests Phalcon\Cache\Adapter\Redis :: get() - wrong index
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterRedisGetSetWrongIndex(): void
    {
        $this->checkExtensionIsLoaded('redis');

        $this->expectException(StorageException::class);
        $this->expectExceptionMessage('Redis server selected database failed');

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

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: get() - errors
     *
     * @return void
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheAdapterStreamGetErrors(): void
    {
        if (version_compare(PHP_VERSION, '8.3.0', '>=')) {
            $this->markTestSkipped('Invalid `unserialize()` will generate warning but still works.');
        }

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
        $this->assertSame($expected, $actual);

        // Invalid stored object
        $actual = file_put_contents(
            $target . 'test-key',
            '{'
        );
        $this->assertNotFalse($actual);

        $expected = 'test';
        $actual   = $adapter->get('test-key', 'test');
        $this->assertSame($expected, $actual);

        // Expiry
        $data = 'Phalcon Framework';

        $actual = $adapter->set('test-key', $data, 1);
        $this->assertTrue($actual);

        sleep(2);

        $expected = 'test';
        $actual   = $adapter->get('test-key', 'test');
        $this->assertSame($expected, $actual);

        $this->safeDeleteFile($target . 'test-key');
    }
}
