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

namespace Phalcon\Tests\Unit\Cache\Cache;

use Phalcon\Cache\AdapterFactory;
use Phalcon\Cache\Cache;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\AbstractUnitTestCase;

use function uniqid;

final class DeleteMultipleTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Cache :: deleteMultiple()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testCacheCacheDeleteMultiple(): void
    {
        $serializer = new SerializerFactory();
        $factory    = new AdapterFactory($serializer);
        $instance   = $factory->newInstance('apcu');

        $adapter = new Cache($instance);

        $key1 = uniqid();
        $key2 = uniqid();
        $key3 = uniqid();
        $key4 = uniqid();

        $adapter->setMultiple(
            [
                $key1 => 'test1',
                $key2 => 'test2',
                $key3 => 'test3',
                $key4 => 'test4',
            ]
        );

        $this->assertTrue($adapter->has($key1));
        $this->assertTrue($adapter->has($key2));
        $this->assertTrue($adapter->has($key3));
        $this->assertTrue($adapter->has($key4));

        $this->assertTrue(
            $adapter->deleteMultiple(
                [
                    $key1,
                    $key2,
                ]
            )
        );

        $this->assertFalse($adapter->has($key1));
        $this->assertFalse($adapter->has($key2));
        $this->assertTrue($adapter->has($key3));
        $this->assertTrue($adapter->has($key4));

        $this->assertTrue($adapter->delete($key3));
        $this->assertTrue($adapter->delete($key4));

        $this->assertFalse(
            $adapter->deleteMultiple(
                [
                    $key3,
                    $key4,
                ]
            )
        );

        $this->assertFalse($adapter->has($key1));
        $this->assertFalse($adapter->has($key2));
        $this->assertFalse($adapter->has($key3));
        $this->assertFalse($adapter->has($key4));
    }
}
