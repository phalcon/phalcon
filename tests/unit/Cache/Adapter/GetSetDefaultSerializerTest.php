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

use Codeception\Example;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Cache\Adapter\Weak;
use Phalcon\Storage\SerializerFactory;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;

final class GetSetDefaultSerializerTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cache\Adapter\* ::
     * getDefaultSerializer()/setDefaultSerializer()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterGetSetDefaultSerializer(
        string $class,
        array $options,
        string $extension
    ): void {
        if (!empty($extension)) {
            $this->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class($serializer, $options);

        $expected = 'php';
        $actual   = $adapter->getDefaultSerializer();
        $this->assertEquals($expected, $actual);

        $adapter->setDefaultSerializer('Base64');
        $expected = 'base64';
        $actual   = $adapter->getDefaultSerializer();
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Weak :: GetSetDefaultSerializer()
     *
     * @return void
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2023-07-17
     */
    public function testCacheAdapterWeakGetSetDefaultSerializerNone(): void
    {
        $serializer = new SerializerFactory();
        $adapter    = new Weak($serializer);

        $actual = $adapter->getDefaultSerializer();
        $this->assertEquals('none', $actual);

        $adapter->setDefaultSerializer('Base64');
        $actual = $adapter->getDefaultSerializer();
        $this->assertEquals('none', $actual);
    }

    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                Apcu::class,
                [],
                'apcu',
            ],
            [
                Libmemcached::class,
                getOptionsLibmemcached(),
                'memcached',
            ],
            [
                Memory::class,
                [],
                '',
            ],
            [
                Redis::class,
                getOptionsRedis(),
                'redis',
            ],
            [
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                '',
            ],
        ];
    }
}
