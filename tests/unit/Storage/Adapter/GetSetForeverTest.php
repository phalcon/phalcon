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

namespace Phalcon\Tests\Unit\Storage\Adapter;

use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\Adapter\Libmemcached;
use Phalcon\Storage\Adapter\Memory;
use Phalcon\Storage\Adapter\Redis;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Adapter\Weak;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\UnitTestCase;
use stdClass;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;
use function uniqid;

final class GetSetForeverTest extends UnitTestCase
{
    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                'class'     => Apcu::class,
                'options'   => [],
                'extension' => 'apcu',
            ],
            [
                'class'     => Libmemcached::class,
                'options'   => getOptionsLibmemcached(),
                'extension' => 'memcached',
            ],
            [
                'class'     => Memory::class,
                'options'   => [],
                'extension' => '',
            ],
            [
                'class'     => Redis::class,
                'options'   => getOptionsRedis(),
                'extension' => 'redis',
            ],
            [
                'class'     => Stream::class,
                'options'   => [
                    'storageDir' => outputDir(),
                ],
                'extension' => '',
            ],
        ];
    }

    /**
     * Tests Phalcon\Storage\Adapter\* :: get()/setForever()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testStorageAdapterGetSetForever(
        string $class,
        array $options,
        string $extension
    ): void {
        if (!empty($extension)) {
            $this->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class($serializer, $options);

        $key = uniqid();

        $result = $adapter->setForever($key, "test");
        $this->assertTrue($result);

        sleep(2);
        $result = $adapter->has($key);
        $this->assertTrue($result);

        /**
         * Delete it
         */
        $result = $adapter->delete($key);
        $this->assertTrue($result);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Weak :: get()setForever()
     *
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2023-07-17
     */
    public function testStorageAdapterWeakGetSetForever(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Weak($serializer);

        $key    = uniqid();
        $obj    = new stdClass();
        $result = $adapter->setForever($key, "test");
        $this->assertFalse($result);
        $result = $adapter->setForever($key, $obj);
        $this->assertTrue($result);
        sleep(2);
        $result = $adapter->has($key);
        $this->assertTrue($result);
        /**
         * Delete it
         */
        $result = $adapter->delete($key);
        $this->assertTrue($result);
    }
}
