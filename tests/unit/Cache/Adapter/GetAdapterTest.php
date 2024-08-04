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
use Memcached as NativeMemcached;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Cache\Adapter\Libmemcached;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Cache\Adapter\Redis;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Cache\Adapter\Weak;
use Phalcon\Storage\SerializerFactory;
use Redis as NativeRedis;

use function getOptionsLibmemcached;
use function getOptionsRedis;
use function outputDir;
use function sprintf;

final class GetAdapterTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Cache\Adapter\* :: getAdapter()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testCacheAdapterGetAdapter(
        string $class,
        array $options,
        mixed $expected,
        string $extension
    ): void {
        if (!empty($extension)) {
            $this->checkExtensionIsLoaded($extension);
        }

        $serializer = new SerializerFactory();
        $adapter    = new $class($serializer, $options);

        $actual = $adapter->getAdapter();

        if (null === $expected) {
            $this->assertNull($actual);
        } else {
            $this->assertInstanceOf($expected, $actual);
        }
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
                null,
                'apcu',
            ],
            [
                Libmemcached::class,
                getOptionsLibmemcached(),
                NativeMemcached::class,
                'memcached',
            ],
            [
                Memory::class,
                [],
                null,
                '',
            ],
            [
                Redis::class,
                getOptionsRedis(),
                NativeRedis::class,
                'redis',
            ],
            [
                Stream::class,
                [
                    'storageDir' => outputDir(),
                ],
                null,
                '',
            ],
            [
                Weak::class,
                [],
                null,
                '',
            ],
        ];
    }
}
